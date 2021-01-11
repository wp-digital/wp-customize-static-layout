(function($, wp, _, settings) {

    'use strict';

    var api = wp.customize;

    api.controlConstructor[settings.type] = api.Control.extend({
        getBaseId: function () {
            return this.setting.id.replace(/([^[]+\[\d+]\[[^\[]+])\[\d+]$/g, '$1');
        },
        getIndexById: function (id) {
            return parseInt(id.replace(/[^[]+\[\d+]\[[^\[]+]\[(\d+)]$/g, '$1'), 10);
        },
        getIndex: function () {
            return this.getIndexById(this.setting.id);
        },
        getNameById: function (id) {
            return id.replace(/[^[]+\[\d+]\[[^\[]+]\[\d+]\[(\w+)]$/g, '$1');
        },
        removeWidget: function () {
            var widget = api.section(this.setting.id);
            var baseId = this.getBaseId();
            var currentIndex = this.getIndex();
            var section = api.section(this.section());
            var indexes = [];
            var widgets = _.filter(section.controls(), function (control) {
                return control.id.lastIndexOf(baseId + '[', 0) === 0;
            });
            var lastWidget = _.last(widgets);

            widget.collapse();

            _.forEach(_.filter(widgets, function (control) {
                var index = this.getIndexById(control.id);

                if (!isNaN(index) && index > currentIndex) {
                    indexes.push(index);

                    return true;
                }

                return false;
            }.bind(this)), function (control, i) {
                var widget = api.section(control.id);

                _.forEach(widget.controls(), function (control) {
                    api(baseId + '[' + (indexes[i] - 1) + ']' + '[' + this.getNameById(control.id) + ']').set(api(control.id).get());
                }.bind(this));
            }.bind(this));

            _.forEach(api.section(lastWidget.id).controls(), function (control) {
                api.remove(control.id);
                api.control(control.id).container.remove();
                api.control.remove(control.id);
            });

            api.remove(lastWidget.id);
            api.control(lastWidget.id).container.remove();
            api.control.remove(lastWidget.id);
            api.section(lastWidget.id).container.remove();
            api.section.remove(lastWidget.id);

            this.removeFromBase(baseId);

            return this;
        },
        removeFromBase: function (id, index) {
            var setting = api(id);
            var prevData = setting.get();
            var newData = _.clone(prevData);

            newData.pop();

            setting.set(newData);
            setting.callbacks.fireWith(setting, [newData, prevData]);

            api.control(id).index -= 1;

            return prevData[index];
        },
        deactivateAllButtons: function () {
            this.container.siblings('.' + this.container[0].className.split(/\s+/).join('.'))
                .find('.' + settings.namespace + '-edit-widget-button')
                .removeClass('active');

            return this;
        },
        maybeToggleDuplication: function () {
            this.container.find('.' + settings.namespace + '-duplicate-widget-button')
                .prop('disabled', _.isEmpty(_.filter(api(this.getBaseId()).get()[this.getIndex()])));

            return this;
        },
        _onClick: function (event) {
            event.preventDefault();
            api.section(this.setting.id).expand();

            return this;
        },
        _onDuplicateClick: function (event) {
            event.preventDefault();
            var baseId = this.getBaseId();
            var widgetId = api.control(baseId).addWidget();

            _.forEach(api.section(this.setting.id).controls(), function (control) {
                api(widgetId + '[' + this.getNameById(control.id) + ']').set(api(control.id).get());
            }.bind(this));
            api.section(widgetId).expand();

            return this;
        },
        _onRemoveClick: function (event) {
            event.preventDefault();

            if (confirm(this.params.l10n.confirmRemove)) {
                this.deactivateAllButtons();
                this.removeWidget();
            }

            return this;
        },
        _onExpanded: function () {
            this.container.find('.' + settings.namespace + '-edit-widget-button').addClass('active');
            this.deactivateAllButtons();

            return this;
        },
        _onCollapsed: function () {
            this.container.find('.' + settings.namespace + '-edit-widget-button').removeClass('active');

            return this;
        },
        ready: function () {
            var section = api.section(this.setting.id);
            var container;

            this.container.find('.' + settings.namespace + '-edit-widget-button').on('click', this._onClick.bind(this));
            this.container.find('.' + settings.namespace + '-duplicate-widget-button').on('click', this._onDuplicateClick.bind(this));
            this.container.find('.' + settings.namespace + '-remove-widget-button').on('click', this._onRemoveClick.bind(this));

            if (section) {
                container = api.section(this.setting.id).container;
                container.on('expanded', this._onExpanded.bind(this));
                container.on('collapsed', this._onCollapsed.bind(this));

                this.maybeToggleDuplication();
                _.forEach(api.section(this.setting.id).controls(), function (control) {
                    api(control.id).bind(this.maybeToggleDuplication.bind(this));
                }.bind(this));
            }

            return this;
        }
    });
})(jQuery, window.wp, _, window.customizeStaticLayoutControlEditWidget);
