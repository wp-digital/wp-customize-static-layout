(function ($, wp, settings) {

    'use strict';

    var api = wp.customize;

    $(function () {
        var onExpanded = function (section) {
            if (section.id.replace(/\[\d+]$/, '').lastIndexOf(settings.panel, 0) === 0) {
                section.container.on('expanded', function (section) {
                    api.previewer.send(settings.panel, section);
                }.bind(null, section.id));
            }
        };

        api.section.each(onExpanded);
        api.section.bind('add', onExpanded);
    });
})(jQuery, window.wp, window.customizeStaticLayout);
