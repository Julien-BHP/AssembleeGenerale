<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity;

final class AssembleeController extends AbstractController
{
    private $doctrine;
    
    public function __construct(ManagerRegistry $doctine) {
        $this->doctrine = $doctine;
    }
    
    #[Route('/', name: 'app_assemblee')]
    public function index(Request $request): Response
    {
        $error = false;
        $user = false;
        $voteAff = false;
        $hasVoted = false;
        
        $config = $this->doctrine->getRepository(Entity\Config::class)->find(1);
        if($config==null){
            return $this->redirectToRoute('setup');
        }
        
        $seance = $this->doctrine->getRepository(Entity\Seance::class)->findOneBy([],['id'=>'DESC'], 1);                
        if($seance == null){
            return $this->redirectToRoute('create_seance');
        }
        
        if($this->getUser()){
            return $this->redirectToRoute('gestion');
        }
        
        $codeAcces = $request->get('codeAcces');
        $openedVote = $this->doctrine->getRepository(Entity\AGPoint::class)->findOpenedVote();
        if ($openedVote) {
            $action = $request->get('action');
            if ($action) {
                $voteForPoint = $request->get('voteForPoint');
                $voteForUser = $request->get('voteForUser');
                $voteValue = $request->get('voteValue');
                $votingUser = $request->get('votingUser');
                if ($voteForPoint && $voteForUser && $voteValue && $votingUser) {
                    $authVote = true;
                    $userToVote = $this->doctrine->getRepository(Entity\Membre::class)->find($voteForUser);
                    $user = $this->doctrine->getRepository(Entity\Membre::class)->find($votingUser);
                    $pointToVote = $this->doctrine->getRepository(Entity\AGPoint::class)->find($voteForPoint);
                    if ($votingUser == $voteForUser) {
                        //VOTE EN SON NOM           
                        if (!$userToVote->isPresent()) {
                            $authVote = false;
                        }
                    } else {
                        //VOTE PAR PROCU
                        if ($userToVote->getProcTo()->getId() != $votingUser) {
                            $authVote = false;
                        }
                    }
                } else {
                    $authVote = false;
                }

                if ($authVote) {
                    $crit = [
                        'user' => $userToVote,
                        'point' => $pointToVote
                    ];
                    $vote = $this->doctrine->getRepository(Entity\Vote::class)->findOneBy($crit);

                    if (!$vote) {
                        //ENCODER VOTE
                        $newVote = new Entity\Vote();
                        $newVote->setPoint($pointToVote);
                        $newVote->setUser($userToVote);

                        $newVoteAnno = new Entity\VoteAnno();
                        $newVoteAnno->setPoint($pointToVote);
                        $newVoteAnno->setDecision($voteValue);
                        $newVoteAnno->setParts($userToVote->getParts());
                        $now = new \DateTime();
                        $newVoteAnno->setVoteTime($now);           
                        
                        $hasVoted = true;

                        $this->doctrine->getManager()->persist($newVote);
                        $this->doctrine->getManager()->persist($newVoteAnno);
                        $this->doctrine->getManager()->flush();
                    }
                } else {
                    return $this->redirectToRoute('assemblee-generale');
                }
            }
        }
        if ($codeAcces) {
            //RECHERCHE UTILISATEUR
            $crit = [
                'accessCode' => $codeAcces
            ];
            $user = $this->doctrine->getRepository(Entity\Membre::class)->findOneBy($crit);
        }
        if ($user) {
           if ($user->isPresent()) {
               if ($openedVote) {
                   //TEST SI VOTE
                    $crit = [
                        'user' => $user,
                        'point' => $openedVote
                    ];

                    $vote = $this->doctrine->getRepository(Entity\Vote::class)->findOneBy($crit);
                    if ($vote) {
                        //TEST SI PROCU
                        if ($user->isRepresent()) {
                            //A des procurations
                            $userRepresent = $user->getProcFor();
                            foreach ($userRepresent as $procu) {
                                if (!$voteAff) {
                                    //TEST SI VOTE
                                    $crit = [
                                        'user' => $procu,
                                        'point' => $openedVote
                                    ];

                                    $vote = $this->doctrine->getRepository(Entity\Vote::class)->findOneBy($crit);

                                    if (!$vote) {
                                        $voteAff = $crit;
                                        $voteAff['proc'] = true;
                                    }
                                }
                            }
                            if (!$voteAff) {
                                if($hasVoted){
                                    $error = 'noMoreVote';
                                }else{
                                    $error = 'alreadyVoted';
                                }                     
                            }
                        }else{
                            if($hasVoted){
                                $error = 'noMoreVote';
                            }else{
                                $error = 'alreadyVoted';
                            } 
                        }
                    }else{
                        $voteAff = $crit;
                        $voteAff['proc'] = false;
                    }
               }else{
                    $error = 'noVoteOpen';
               }
           }else{
               $error = 'notPresent';
           }
        }else {
            if($codeAcces){
                $error = 'noUser';
            }else{
                $error = 'noAccessCode';
            }            
        }
        
        return $this->render('assemblee/index.html.twig', [
            'assemblee' => $seance,
            'error' => $error,
            'openedVote' => $openedVote,
            'voteAff' => $voteAff,
            'user' => $user,
            'config' => $config
        ]);
    }
    
    #[Route('gestion', name: 'gestion')]
    public function gestion(Request $request): Response
    {
        $submit = $request->get('action');
        $config = $this->doctrine->getRepository(Entity\Config::class)->find(1);
        $seance = $this->doctrine->getRepository(Entity\Seance::class)->findOneBy([], ['id' => 'DESC'], 1);
        
        if ($submit) {
            $userToPresent = $request->get('userPresent');
            if ($userToPresent) {
                $user = $this->doctrine->getRepository(Entity\Membre::class)->find($userToPresent);
                $user->setPresent(true);
                $now = new \DateTime();
                $user->setDateArrivee($now);
                $this->doctrine->getManager()->persist($user);
                $this->doctrine->getManager()->flush();
            }else{
                $procFrom = $request->get('procFrom');
                $procTo = $request->get('procTo');
                if ($procFrom && $procTo) {
                    $procFromUser = $this->doctrine->getRepository(Entity\Membre::class)->find($procFrom);
                    $procToUser = $this->doctrine->getRepository(Entity\Membre::class)->find($procTo);
                    $procFromUser->setProcTo($procToUser);
                    $procToUser->setIsRepresent(true);                    
                    $this->doctrine->getManager()->persist($procFromUser);
                    $this->doctrine->getManager()->persist($procToUser);
                    $this->doctrine->getManager()->flush();
                }else {
                    switch ($submit) {
                        case "start":
                            $now = new \DateTime();
                            $seance->setDebut($now);
                            $this->doctrine->getManager()->persist($seance);
                            $this->doctrine->getManager()->flush();
                            return $this->redirectToRoute('gestion');
                            break;
                        case "end":
                            $now = new \DateTime();
                            $seance->setFin($now);
                            $this->doctrine->getManager()->persist($seance);
                            $this->doctrine->getManager()->flush();
                            return $this->redirectToRoute('gestion');
                            break;
                        case "openVote":
                            $pointToVote = $request->get('point');
                            $point = $this->doctrine->getRepository(Entity\AGPoint::class)->find($pointToVote);
                            $now = new \DateTime();
                            $point->setVoteOpen($now);
                            $this->doctrine->getManager()->persist($point);
                            $this->doctrine->getManager()->flush();
                            return $this->redirectToRoute('gestion');
                            break;
                        case "closeVote":
                            $pointToClose = $request->get('point');
                            $point = $this->doctrine->getRepository(Entity\AGPoint::class)->find($pointToClose);
                            $now = new \DateTime();
                            $point->setVoteClose($now);
                            $this->doctrine->getManager()->persist($point);
                            $this->doctrine->getManager()->flush();
                            return $this->redirectToRoute('gestion');
                            break;
                    }
                }
            }
        }
        
        $nbParts = 0;
        $nbPartsPresentes = 0;

        $membres = $this->doctrine->getRepository(Entity\Membre::class)->findAll();
        $possibleVotes = 0;
        
        foreach ($membres as $user) {
            $nbParts += $user->getParts();
            if ($user->isPresent() || $user->getProcTo()) {
                $nbPartsPresentes += $user->getParts();
                $possibleVotes++;
            }
        }
        $percentPresent = 0;
        if($nbParts>0){
            $percentPresent = $nbPartsPresentes / $nbParts * 100;
        }
        $crit = [
            'present' => 0,
            'procTo' => null
        ];
        $nonPresents = $this->doctrine->getRepository(Entity\Membre::class)->findBy($crit, ['nom' => 'ASC']);
        $crit = [
            'present' => 1,
        ];
        $presents = $this->doctrine->getRepository(Entity\Membre::class)->findBy($crit, ['nom' => 'ASC']);
        $openedVoteDetail = false;
        $openedVote = $this->doctrine->getRepository(Entity\AGPoint::class)->findOpenedVote();
        if ($openedVote) {
            $openedVoteDetail = [];
            $openedVoteDetail['point'] = $openedVote;
            $openedVoteDetail['maxVotes'] = $possibleVotes;
            $votes = $this->doctrine->getRepository(Entity\Vote::class)->findBy(['point'=>$openedVote]);
            $openedVoteDetail['nbVotes'] = count($votes);
            $votesAnno = $this->doctrine->getRepository(Entity\VoteAnno::class)->findBy(['point'=>$openedVote]);
            $openedVoteDetail['totalParts'] = $nbPartsPresentes;
            $nbPour = 0;
            $nbAbst = 0;
            $nbContre = 0;
            foreach ($votesAnno as $vote) {
                switch ($vote->getDecision()) {
                    case 'pour':
                        $nbPour += $vote->getParts();
                        break;
                    case 'abs':
                        $nbAbst += $vote->getParts();
                        break;
                    case 'contre':
                        $nbContre += $vote->getParts();
                        break;
                }
            }
            $openedVoteDetail['nbPour'] = $nbPour;
            $openedVoteDetail['nbAbst'] = $nbAbst;
            $openedVoteDetail['nbContre'] = $nbContre;
            $openedVoteDetail['percentPour'] = $nbPour / $nbPartsPresentes * 100;
            $openedVoteDetail['percentAbst'] = $nbAbst / $nbPartsPresentes * 100;
            $openedVoteDetail['percentContre'] = $nbContre / $nbPartsPresentes * 100;
        }
        $closedVotes = $this->doctrine->getRepository(Entity\AGPoint::class)->findClosedVotes();
        $closedVotesDetail = false;
        if ($closedVotes) {
            $closedVotesDetail = [];
            foreach ($closedVotes as $closedVote) {
                $closedVotesDetail[$closedVote->getId()] = [];
                $closedVotesDetail[$closedVote->getId()]['maxVotes'] = $possibleVotes;
                $crit = [
                    'point' => $closedVote
                ];
                $votes = $this->doctrine->getRepository(Entity\Vote::class)->findBy($crit);
                $closedVotesDetail[$closedVote->getId()]['nbVotes'] = count($votes);
                $votesAnno = $this->doctrine->getRepository(Entity\VoteAnno::class)->findBy($crit);
                $closedVotesDetail[$closedVote->getId()]['totalParts'] = $nbPartsPresentes;
                $nbPour = 0;
                $nbAbst = 0;
                $nbContre = 0;
                foreach ($votesAnno as $vote) {
                    switch ($vote->getDecision()) {
                        case 'pour':
                            $nbPour += $vote->getParts();
                            break;
                        case 'abs':
                            $nbAbst += $vote->getParts();
                            break;
                        case 'contre':
                            $nbContre += $vote->getParts();
                            break;
                    }
                }
                $closedVotesDetail[$closedVote->getId()]['nbPour'] = $nbPour;
                $closedVotesDetail[$closedVote->getId()]['nbAbst'] = $nbAbst;
                $closedVotesDetail[$closedVote->getId()]['nbContre'] = $nbContre;                
                $closedVotesDetail[$closedVote->getId()]['percentPour'] = $nbPour / $nbPartsPresentes * 100;
                $closedVotesDetail[$closedVote->getId()]['percentAbst'] = $nbAbst / $nbPartsPresentes * 100;
                $closedVotesDetail[$closedVote->getId()]['percentContre'] = $nbContre / $nbPartsPresentes * 100;
            }
        }
        $crit = [
            "seance" => $seance
        ];
        $points = $this->doctrine->getRepository(Entity\AGPoint::class)->findBy($crit, ["ordre" => "ASC"]);
        return $this->render('assemblee/gestion.html.twig', [
            'config' => $config,
            'assemblee' => $seance,
            'points' => $points,
            'nonPresents' => $nonPresents,
            'presents' => $presents,
            'parts' => $nbParts,
            'partsPresentes' => $nbPartsPresentes,
            'percentPresent' => $percentPresent,
            'openedVote' => $openedVoteDetail,
            'closedVotes' => $closedVotesDetail
        ]);        
    }
    
    #[Route('create-seance', name: 'create_seance')]
    public function createSeance(Request $request): Response
    {
        $config = $this->doctrine->getRepository(Entity\Config::class)->find(1);                
        
        $submit = $request->get('submit');        
        if($submit){
            $date = \DateTime::createFromFormat('d/m/Y H:i', $request->get('date'));
            $extra = $request->get('extra');
            $seance = new Entity\Seance();
            $seance->setDate($date);     
            if($extra==1){
                $seance->setExtra(true);
            }
            $this->doctrine->getManager()->persist($seance);
            $this->doctrine->getManager()->flush();
            return $this->redirectToRoute('app_assemblee');
        }
        
        return $this->render('assemblee/create.html.twig',['config' => $config]);
    }
    
    #[Route('update-seance', name: 'update_seance')]
    public function updateSeance(Request $request): Response
    {
        $config = $this->doctrine->getRepository(Entity\Config::class)->find(1);
        $seance = $this->doctrine->getRepository(Entity\Seance::class)->findOneBy([], ['id' => 'DESC'], 1);
        $points = $this->doctrine->getRepository(Entity\AGPoint::class)->findBy(['seance'=>$seance], ['ordre'=>'ASC']);
        $membres = $this->doctrine->getRepository(Entity\Membre::class)->findAll();
        $submit = $request->get('submit');
        if($submit){
            $date = \DateTime::createFromFormat('d/m/Y H:i', $request->get('date'));
            $extra = $request->get('extra');
            $seance->setDate($date);     
            if($extra==1){
                $seance->setExtra(true);
            }else{
                $seance->setExtra(false);
            }
            $this->doctrine->getManager()->persist($seance);
            $this->doctrine->getManager()->flush();
        }
        
        return $this->render('assemblee/update.html.twig',['config' => $config,'seance'=>$seance,'points'=>$points,'membres'=>$membres]);
    }
    
    #[Route('reset-seance', name: 'reset_seance')]
    public function resetSeance(Request $request): Response
    {
        $seance = $this->doctrine->getRepository(Entity\Seance::class)->findOneBy([], ['id' => 'DESC'], 1);
        $seance->setDebut(null);
        $seance->setFin(null);
        $this->doctrine->getManager()->persist($seance);
        $membres = $this->doctrine->getRepository(Entity\Membre::class)->findAll();
        foreach ($membres as $mem){
            $mem->setProcTo(null);
            $mem->setPresent(false);
            $mem->setDateArrivee(null);
            $mem->setIsRepresent(false);
            $this->doctrine->getManager()->persist($mem);
        }
        $votes = $this->doctrine->getRepository(Entity\Vote::class)->findAll();
        foreach ($votes as $v){
            $this->doctrine->getManager()->remove($v);
        }
        $votesAnno = $this->doctrine->getRepository(Entity\VoteAnno::class)->findAll();
        foreach ($votesAnno as $v){
            $this->doctrine->getManager()->remove($v);
        }
        $keepPoints = $request->get('keepPoints');
        foreach ($seance->getPoints() as $point){
            if($keepPoints == 1){
                $point->setVoteOpen(null);
                $point->setVoteClose(null);
                $this->doctrine->getManager()->persist($point);
            }else{
                $this->doctrine->getManager()->remove($point);
            }
        }
        $this->doctrine->getManager()->flush();
        return $this->redirectToRoute('update_seance');
    }
    
    #[Route('reload-membres', name: 'reload_membres')]
    public function reloadMembres(Request $request): Response
    {
        $file = $_FILES['membres'];        
        $row = 1;
        $membres = [];
        if (($csv = fopen($file['tmp_name'], "r")) !== FALSE) {
            while (($data = fgetcsv($csv, 1000, ";")) !== FALSE) {
                $num = count($data); 
                if($row>1){
                    $membres[] = $data;
                }
                $row++;
            }
            fclose($csv);
        }
        if(count($membres)>0){
            $membresObj = $this->doctrine->getRepository(Entity\Membre::class)->findAll();
            foreach ($membresObj as $mem){
                $this->doctrine->getManager()->remove($mem);
            }
            $this->doctrine->getManager()->flush();
            foreach ($membres as $mem){
                $newMembre = new Entity\Membre();
                $newMembre->setUsername($mem[0]);
                $newMembre->setNom($mem[1]);
                $newMembre->setType($mem[2]);
                $newMembre->setAccessCode($mem[3]);
                $newMembre->setParts($mem[4]);
                $this->doctrine->getManager()->persist($newMembre);
            }
            $this->doctrine->getManager()->flush();
        }
        return $this->redirectToRoute('update_seance');
    }
    
    #[Route('add-point', name: 'add_point')]
    public function addPoint(Request $request): Response
    {
        $titre = $request->get('titre');
        $vote = $request->get('vote');
        $action = $request->get('action');
        
        if($action=='add'){
            $seance = $this->doctrine->getRepository(Entity\Seance::class)->findOneBy([], ['id' => 'DESC'], 1);
            $point = new Entity\AGPoint();
            $point->setSeance($seance);
            $point->setTitre($titre);
            if($vote==1){
                $point->setHasVote(true);
            }else{
                $point->setHasVote(false);
            }
            $maxOrdre = $this->doctrine->getRepository(Entity\AGPoint::class)->findMaxOrdre();
            $maxOrdre = $maxOrdre[1];
            $point->setOrdre($maxOrdre+1);
            $this->doctrine->getManager()->persist($point); 
        }else{
            $pointId = $request->get('point');
            $point = $this->doctrine->getRepository(Entity\AGPoint::class)->find($pointId);
            $point->setTitre($titre);
            if($vote==1){
                $point->setHasVote(true);
            }else{
                $point->setHasVote(false);
            }
            $this->doctrine->getManager()->persist($point);            
        }
        $this->doctrine->getManager()->flush();
        return $this->redirectToRoute('update_seance');
    }
    
    #[Route('move-point', name: 'move_point')]
    public function movePoint(Request $request): Response
    {
        $point = $request->get('point');
        $action = $request->get('action');
        
        $pointObj = $this->doctrine->getRepository(Entity\AGPoint::class)->find($point);
        if($action=='up'){
            $pointToMove = $this->doctrine->getRepository(Entity\AGPoint::class)->findOneBy(['ordre'=>$pointObj->getOrdre()-1]);
            $pointObj->setOrdre($pointToMove->getOrdre());
            $pointToMove->setOrdre($pointToMove->getOrdre()+1);
        }else{
            $pointToMove = $this->doctrine->getRepository(Entity\AGPoint::class)->findOneBy(['ordre'=>$pointObj->getOrdre()+1]);
            $pointObj->setOrdre($pointToMove->getOrdre());
            $pointToMove->setOrdre($pointToMove->getOrdre()-1);
        }
        $this->doctrine->getManager()->persist($pointObj);
        $this->doctrine->getManager()->persist($pointToMove);
        $this->doctrine->getManager()->flush();        
        return $this->redirectToRoute('update_seance');
    }
    
    #[Route('delete-point', name: 'delete_point')]
    public function deletePoint(Request $request): Response
    {
        $point = $request->get('point');
        $pointObj = $this->doctrine->getRepository(Entity\AGPoint::class)->find($point);
        $ordre = $pointObj->getOrdre();
        $this->doctrine->getManager()->remove($pointObj);
        $pointsToMove = $this->doctrine->getRepository(Entity\AGPoint::class)->findOrdreSup($ordre);
        foreach ($pointsToMove as $p){
            $p->setOrdre($p->getOrdre()-1);
            $this->doctrine->getManager()->persist($p);
        }
        $this->doctrine->getManager()->flush();
        return $this->redirectToRoute('update_seance');
    }
    
    #[Route('setup', name: 'setup')]
    public function setup(Request $request, UserPasswordHasherInterface $encoder): Response
    {
        $config = $this->doctrine->getRepository(Entity\Config::class)->find(1);
        if(!$config){
            $config = new Entity\Config();
            $this->doctrine->getManager()->persist($config);
            $this->doctrine->getManager()->flush();
        }
        $admin = $this->doctrine->getRepository(Entity\User::class)->find(1);
        $submit = $request->get('submit');
        if($submit){
            $nom = $request->get('nom');
            $email = $request->get('admin');
            $password = $request->get('password');
            $password_again = $request->get('password_again');
                  
            $config->setSociete($nom);
            
            $logo = $_FILES['logo'];
            if($logo['name']!=''){
                $ext = pathinfo($logo['name'], PATHINFO_EXTENSION);
                move_uploaded_file($logo['tmp_name'], 'logo/logo.'.$ext); 
                $config->setLogo('logo/logo.'.$ext);
            }            
            if(!$admin){
                $admin = new Entity\User();
                $admin->setRoles(['ROLE_ADMIN']);
            }
            if($email){
                $admin->setEmail($email);
            }            
            $error = false;
            if($password){
                if($password==$password_again){                    
                    $hash = $encoder->hashPassword($admin, $password);
                    $admin->setPassword($hash);
                }else{
                    $error = 'NotSame';
                }
            } 
            
            $this->doctrine->getManager()->persist($admin);
            $this->doctrine->getManager()->persist($config);
            $this->doctrine->getManager()->flush();
            
            if(!$error){
                return $this->redirectToRoute('app_assemblee');
            }
        }
        
        return $this->render('assemblee/setup.html.twig', ['config' => $config, 'admin' => $admin]);
    }
}
