/*
 * Prestafa
 *
 * */
$('document').ready(function(){
    // Brithday Jalali
    if( $('select[name=years]').length )
    {
        synsBrithday.init();
        $("button[name=submitIdentity]").closest("form").submit(function() {
            synsBrithday.setDateBrithdayNew();
        });

        $("button[name=submitAccount]").closest("form").submit(function() {
            synsBrithday.setDateBrithdayNew();
        });
    }
});
