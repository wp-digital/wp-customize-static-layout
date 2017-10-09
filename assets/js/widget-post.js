(function ($, wp, settings) {

    'use strict';

    var api = wp.customize;

    $(function () {
        var exclude = [];

        _.each(settings.ids, function (id) {
            var setting = api(id);
            var index = exclude.length;

            exclude[index] = setting.get() || 0;
            setting.bind(function (index, value) {
                exclude[index] = value || 0;
            }.bind(null, index));
            api.control(id, function (control) {
                control.params.post_query_vars = control.params.post_query_vars || {};
                control.params.post_query_vars.dropdown_args = control.params.post_query_vars.dropdown_args || {};
                control.params.post_query_vars.post__not_in = exclude;
                control.params.post_query_vars.dropdown_args.exclude = exclude;
                control.params.post_query_vars.dropdown_args.include =control.params.post_query_vars.post__in ;
            });
        });
    });
})(jQuery, window.wp, window.customizeStaticLayoutWidgetPost);