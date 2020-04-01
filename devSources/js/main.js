(function() {
    'use strict';
        window.setTimeout(function () {
            $('.autoclose').fadeTo(500, 0).slideUp(1000, function () {
                $(this).remove();
            });
        }, 5000);


    $( document ).ready(function() {
        if ($(".toast").hasClass("toast")) {
            $('.toast').toast('show');
        }
    });
})();