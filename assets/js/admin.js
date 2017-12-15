(function ($, wp, settings) {

    'use strict';

    var api = wp.customize;

    $(function () {
        api.section.each(function (section) {
            if (section.id.replace(/\[\d+]$/, '').lastIndexOf(settings.panel, 0) === 0) {
                section.expanded.bind('expanded', function (section, isExpanded) {
                    if (isExpanded) {
                        api.previewer.send(settings.panel, section);
                    }
                }.bind(null, section.id));
            }
        });
    });
})(jQuery, window.wp, window.customizeStaticLayout);