/*jslint nomen: true*/
/*global define*/
define(function(require) {
    'use strict';

    var ConnectButtonComponent,
        WidgetComponent = require('oroui/js/app/components/widget-component'),
        mediator = require('oroui/js/mediator');

    ConnectButtonComponent = WidgetComponent.extend({
        defaults: {
            type: 'dialog',
            options: {
                stateEnabled: false,
                incrementalPosition: false,
                loadingMaskEnabled: true,
                dialogOptions: {
                    modal: true,
                    resizable: false,
                    width: 510,
                    autoResize: true
                }
            }
        },

        _bindEnvironmentEvent: function(widget) {
            var message = this.options.message;

            this.listenTo(widget, 'formSave', function() {
                widget.remove();
                if (message) {
                    mediator.execute('addMessage', 'success', message);
                }
                mediator.execute('refreshPage');
            });
        }
    });

    return ConnectButtonComponent;
});
