(function ($, wp, settings) {

    'use strict';

    var api = wp.customize;
    var init = function () {
        var $body = $('body');
        var $doc = $body.add('html');
        var $window = $(window);

        var clearHightlighing = function () {
            $body
                .find('.widget-customizer-highlighted-widget')
                .removeClass('widget-customizer-highlighted-widget');
        };

        api.preview.bind(settings.panel, function (widget) {
            var $widget = $('[data-widget="' + widget + '"]');
            var top = $widget.length ? $widget.offset().top : -1;
            var height = $widget.outerHeight(true);
            var viewportHeight = $window.height();

            if (top !== -1) {
                $doc.animate({
                    scrollTop: Math.min(top, top - viewportHeight / 2 + height / 2)
                });
                clearHightlighing();
                $widget.addClass('widget-customizer-highlighted-widget');
            }
        });
        $body.on('click', clearHightlighing);
    };

    $(function () {
        if (api.preview) {
            init();
        }
    });
})(jQuery, window.wp, window.customizeStaticLayoutPreview);