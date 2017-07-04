(function($) {
    $(document).ready(function() {

        $overlay = $('#overlay');
        $nav = $('#main-nav');

        $nav.on("show.r.navigation", function(event) {
            $overlay.addClass('active');
        });

        $nav.on("shown.r.navigation", function(event) {
        });

        $nav.on("hide.r.navigation", function(event) {
            $overlay.removeClass('active');
        });

        $nav.on("hidden.r.navigation", function(event) {
        });

        $('.navigation__link ').click(function(){
            $nav.navigation('hide');
        });

    });

})(jQuery);
