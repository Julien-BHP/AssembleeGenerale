// Forms Datepicker
$.fn.datepicker.languages['fr'] = {
  format: 'dd/mm/yyyy',
  days: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
  daysShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
  daysMin: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
  months: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
  monthsShort: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jui', 'Jul', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec'],
  weekStart: 1,
  startView: 0,
  yearFirst: true,
  yearSuffix: ''
};

$(document).ready(() => {
    // Datepicker
    $('[data-toggle="datepicker"]').datepicker({
        language: 'fr'
    });

    $('[data-toggle="datetimepicker"]').daterangepicker({
        singleDatePicker: true,
        timePicker: true,
        showDropdowns: true,
        timePicker24Hour: true,
        timePickerIncrement: 15,
        locale: {
            format: "DD/MM/YYYY HH:mm",
            cancelLabel: 'Annuler',
            applyLabel: "Sélectionner",
            weekLabel: "Sem",
            daysOfWeek: [
                "Dim",
                "Lun",
                "Mar",
                "Mer",
                "Jeu",
                "Ven",
                "Sam"
            ],
            monthNames: [
                "Janvier",
                "Février",
                "Mars",
                "Avril",
                "Mai",
                "Juin",
                "Juillet",
                "Août",
                "Septembre",
                "Octobre",
                "Novembre",
                "Decembre"
            ],
            firstDay: 1
        },
        buttonClasses: "btn btn-success",
        cancelClass: "btn-link bg-transparent rm-border text-danger",
    });

});
