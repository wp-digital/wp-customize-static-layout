(function ($, wp) {

    'use strict';

    var api = wp.customize;

    $(function () {
        var exclude = {};
        var onChange = function (id, value) {
            exclude[id] = value || 0;
        };
        var setControlQueryParams = function (control) {
            control.params.post_query_vars = control.params.post_query_vars || {};
            control.params.post_query_vars.dropdown_args = control.params.post_query_vars.dropdown_args || {};
            control.params.post_query_vars.dropdown_args.exclude = control.params.post_query_vars.post__not_in = _.values(exclude);
            control.params.post_query_vars.dropdown_args.include = control.params.post_query_vars.post__in;
        };

        api.control.bind('add', function (control) {
            var setting;

            if (control.params.type === 'object_selector') {
                setting = api(control.setting.id);
                exclude[control.setting.id] = setting.get() || 0;
                setting.bind(onChange.bind(null, control.setting.id));
                api.control(control.id, setControlQueryParams);
            }
        });
    });
})(jQuery, window.wp);
