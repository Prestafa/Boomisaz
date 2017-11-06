/*
 * Prestafa
 *
 * */
$('document').ready(function(){
    if( typeof psf_plus_jalali_status !== 'undefined' && psf_plus_jalali_status == '1' )
    {
        var input = $('input.datepicker, input.datetimepicker, #calendar input.date-input,.datepicker input');
        window.formatPersian = false;

        input.closest("form").submit(function() {
            input.each(function(){
                var value = $(this).val();
                if( value !== '' && value !== '0000-00-00 00:00:00' && value !== '0000-00-00' ) {
                    var date = new DateJalali ( persiamNumberToEnglish(value) );
                    $(this).val( date.getDate() );
                }

            });
        });
        input.each(function(){
            var value = $(this).val();
            if( value !== '' && value !== '0000-00-00 00:00:00' && value !== '0000-00-00' ){
                var date = new DateJalali ( persiamNumberToEnglish(value) );
                $(this).val( date.getJalali() );
            }

        });
        $('.datepicker1,.datepicker2').each( function() {
            var date = new DateJalali ( $(this).data('date') );
            $(this).data('date', date.getJalali() );
        });
        $('#datepicker-from-info,#datepicker-to-info').each(function(){
            var date = new DateJalali ( $(this).html() );
            $(this).html(date.getJalali());
        });

        // Brithday Jalali
        if( $('select[name=years]').length )
        {
            synsBrithday.init();
            $('#customer_form').submit(function() {
                synsBrithday.setDateBrithdayNew();
            });
        }


    }
});

function persiamNumberToEnglish(value) {
    if (!value) {
        return;
    }
    var arabicNumbers = ["١", "٢", "٣", "٤", "٥", "٦", "٧", "٨", "٩", "٠"],
        persianNumbers = ["۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹", "۰"];

    for (var i = 0, numbersLen = arabicNumbers.length; i < numbersLen; i++) {
        value = value.replace(new RegExp(arabicNumbers[i], "g"), persianNumbers[i]);
    }

    var persianNumbers = ["۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹", "۰"],
        englishNumbers = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0"];

    for (var i = 0, numbersLen = persianNumbers.length; i < numbersLen; i++) {
        value = value.replace(new RegExp(persianNumbers[i], "g"), englishNumbers[i]);
    }

    return value;
}