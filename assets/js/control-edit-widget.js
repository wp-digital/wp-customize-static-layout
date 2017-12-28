(function($, wp, _, settings) {

    'use strict';

    var api = wp.customize;

    api.controlConstructor[settings.type] = api.Control.extend({
        removeWidget: function () {
            var widget = api.section(this.setting.id);
            var baseId = widget.id.replace(/([^[]+\[\d+]\[[^\[]+])\[\d+]$/g, '$1');
            var indexRegExp = /[^[]+\[\d+]\[[^\[]+]\[(\d+)]$/g;
            var currentIndex = parseInt(widget.id.replace(indexRegExp, '$1'), 10);
            var section = api.section(this.section());
            var indexes = [];
            var widgets = _.filter(section.controls(), function (control) {
                return control.id.lastIndexOf(baseId + '[', 0) === 0;
            });
            var lastWidget = _.last(widgets);

            widget.collapse();

            _.forEach(_.filter(widgets, function (control) {
                var index = parseInt(control.id.replace(indexRegExp, '$1'), 10);

                if (!isNaN(index) && index > currentIndex) {
                    indexes.push(index);

                    return true;
                }

                return false;
            }.bind(this)), function (control, i) {
                var widget = api.section(control.id);

                _.forEach(widget.controls(), function (control) {
                    var name = control.id.replace(/[^[]+\[\d+]\[[^\[]+]\[\d+]\[(\w+)]$/g, '$1');

                    api(baseId + '[' + (indexes[i] - 1) + ']' + '[' + name + ']').set(api(control.id).get());
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
        _onClick: function (event) {
            event.preventDefault();
            var widget = this.setting.id;

            api.section(widget).expand();

            return this;
        },
        _onRemoveClick: function (event) {
            event.preventDefault();

            if (confirm(this.params.l10n.confirmRemove)) {
                this.removeWidget();
            }

            return this;
        },
        ready: function () {
            this.container.find('.' + settings.namespace + '-edit-widget-button').on('click', this._onClick.bind(this));
            this.container.find('.' + settings.namespace + '-remove-widget-button').on('click', this._onRemoveClick.bind(this));

            return this;
        }
    });
})(jQuery, window.wp, _, window.customizeStaticLayoutControlEditWidget);