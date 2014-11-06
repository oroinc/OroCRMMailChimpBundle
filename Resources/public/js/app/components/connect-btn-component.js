/*jslint nomen: true*/
/*global define*/
define(function (require) {
    'use strict';

    var DialogWidget = require('oro/dialog-widget'),
        mediator = require('oroui/js/mediator');

    return function (options) {
        var btn = options._sourceElement,
            message = btn.data('message'),
            title = btn.data('title'),
            url = btn.data('url');

        btn.on('click', function () {
            var dialogOptions = {
                title: title,
                url: url,
                stateEnabled: false,
                incrementalPosition: false,
                loadingMaskEnabled: true,
                dialogOptions: {
                    modal: true,
                    resizable: false,
                    width: 475,
                    autoResize: true
                }
            };
            var dialog = new DialogWidget(dialogOptions);
            dialog.on('formSave', function () {
                if (message) {
                    mediator.execute('addMessage', 'success', message);
                }
                mediator.execute('refreshPage');
                dialog.remove();
            });
            dialog.render();
        });
    };
});
