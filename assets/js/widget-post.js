(function ($, wp) {

    'use strict';

    var api = wp.customize;

    $(function () {
        var exclude = [];
        var onChange = function (control, value) {
            var section = control.setting.id.split('][')[1] || null;
            exclude[section][control.setting.id] = value || 0;
            setControlQueryParams(control);
        };
        var setControlQueryParams = function (control) {
            var section = control.id.split('][')[1] || null;
            control.params.post_query_vars = control.params.post_query_vars || {};
            control.params.post_query_vars.dropdown_args = control.params.post_query_vars.dropdown_args || {};
            control.params.post_query_vars.dropdown_args.exclude = control.params.post_query_vars.post__not_in = _.values(exclude[section]);
            control.params.post_query_vars.dropdown_args.include = control.params.post_query_vars.post__in;
        };

        api.control.bind('add', function (control) {
            var setting;

            if (control.params.type === 'object_selector') {
                var section = control.id.split('][')[1] || null;

                if (exclude[section] === undefined) {
                    exclude[section] = [];
                }

                setting = api(control.setting.id);
                exclude[section][control.setting.id] = setting.get() || 0;
                setting.bind(onChange.bind(null, control));
                api.control(control.id, setControlQueryParams);
            }
        });
    });
})(jQuery, window.wp);
