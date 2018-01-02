(function($, wp, _, settings, editWidgetSettings) {

    'use strict';

    var api = wp.customize;

    api.controlConstructor[settings.type] = api.Control.extend({
        getWidgetIndexes: function () {
            var indexes = _.keys(this.setting());

            return indexes.length ? _.filter(_.map(indexes, function (key) {
                return parseInt(key, 10);
            }), function (key) {
                return !isNaN(key);
            }).sort() : [];
        },
        getLastWidgetIndex: function () {
            var indexes = this.getWidgetIndexes();

            return indexes.length ? _.max(this.getWidgetIndexes()) : -1;
        },
        getParentContainer: function () {
            var widget = api.section(this.section());

            return widget.contentContainer.is('ul') ? widget.contentContainer : widget.contentContainer.find('ul:first');
        },
        addWidget: function () {
            var widget = this.setting.id;
            var sectionParams = _.clone(api.section(widget).params);
            var section = sectionParams.id + '[' + this.index + ']';

            _.forEach(api.section(widget).controls(), function (control) {
                control = this.addControl(section, control);
                api(control.id).bind(function (index, value) {
                    var setting = api(widget);
                    var prevData = setting.get();
                    var newData = _.clone(prevData);

                    if (!_.isArray(newData)) {
                        newData = [];
                    }

                    if (!_.isObject(newData[index])) {
                        newData[index] = {};
                    }

                    newData[index][control.name] = value;
                    setting.set(newData);
                    setting.callbacks.fireWith(setting, [newData, prevData]);
                }.bind(this, this.index));
            }.bind(this));

            delete sectionParams.id;
            delete sectionParams.instanceNumber;

            sectionParams.title += ' #' + (this.index + 1);
            api.section.add(new api.OuterSection(section, sectionParams));

            this.addEditButton(section);
            this.index += 1;

            return section;
        },
        addControl: function (section, control) {
            var settings = this.setting();
            var controlParams = _.extend({}, control.params, {
                section: section
            });
            var setting = controlParams.settings.default;
            var id = setting.replace(/([^[]+\[\d+]\[[^\[]+])(\[\w+])$/g, '$1[' + this.index + ']$2');
            var name = setting.replace(/[^[]+\[\d+]\[[^\[]+]\[(\w+)]$/g, '$1');
            var settingParams = _.clone(api.settings.settings[setting]);
            var value = '';
            var choices = {};

            delete settingParams.value;

            if (_.has(settings, this.index) && _.has(settings[this.index], name)) {
                value = settings[this.index][name];
            }

            api.add(new api.Setting(id, value, settingParams));

            if (_.contains(['select'], controlParams.type)) {
                $(controlParams.content).find('option').each(function (index, el) {
                    var $el = $(el);

                    choices[$el.attr('value')] = $el.html();
                });
                controlParams.choices = choices;
            }

            delete controlParams.content;
            delete controlParams.settings;
            delete controlParams.instanceNumber;

            controlParams.setting = id;
            api.control.add(new api.Control(id, controlParams));

            return {
                id: id,
                name: name
            };
        },
        addEditButton: function (section) {
            var id = '_' + this.setting.id;
            var controlParams = _.extend({}, api.control(id).params, {
                active: true
            });
            var settingParams = _.clone(api.settings.settings[id]);

            delete settingParams.value;

            api.add(new api.Setting(section, null, settingParams));

            delete controlParams.content;
            delete controlParams.settings;
            delete controlParams.instanceNumber;

            controlParams.setting = section;
            api.control.add(new api.controlConstructor[editWidgetSettings.type](section, controlParams));

            return section;
        },
        initializeSortable: function () {
            var parentContainer = this.getParentContainer();

            parentContainer.sortable({
                handle: '.' + settings.namespace + '-sort-widget-button',
                items: '> li.customize-control-' + settings.namespace + '_edit_widget',
                cancel: 'input,textarea,button:not(.' + settings.namespace + '-sort-widget-button),select,option',
                stop: this._onSort.bind(this)
            });
            parentContainer.disableSelection();

            return this;
        },
        _onClick: function (event) {
            event.preventDefault();
            api.section(this.addWidget()).expand();

            return this;
        },
        _onSort: function () {
            var parentContainer = this.getParentContainer();
            var $widgets = parentContainer.find('> li.customize-control-' + settings.namespace + '_edit_widget').filter(':visible');
            var widgets = _.filter(api.section(this.section()).controls(), function (control) {
                return control.id.lastIndexOf(this.id + '[', 0) === 0;
            }.bind(this));
            var values = {};

            _.forEach(widgets, function (widget) {
                _.reduce(api.section(widget.id).controls(), function (values, control) {
                    values[control.id] = api(control.id).get();

                    return values;
                }, values)
            });
            _.forEach(widgets, function (widget) {
                var index = $widgets.index(widget.container);
                var section = api.section(widget.id);

                if (section.expanded()) {
                    section.collapse();
                }

                _.forEach(section.controls(), function (control) {
                    api(control.id.replace(/([^[]+\[\d+]\[[^\[]+])\[\d+](\[\w+])$/g, '$1[' + index + ']$2')).set(values[control.id]);
                });
            });

            parentContainer.sortable('cancel');

            return this;
        },
        ready: function () {
            var lastIndex = this.getLastWidgetIndex();
            var addWidget;

            this.index = 0;

            if (lastIndex > -1) {
                addWidget = this.addWidget.bind(this);
                _.forEach(_.range(lastIndex + 1), addWidget);
            }

            this.initializeSortable();
            this.container.find('.button').on('click', this._onClick.bind(this));

            return this;
        }
    });
})(jQuery, window.wp, _, window.customizeStaticLayoutControlAddWidget, window.customizeStaticLayoutControlEditWidget);