/*jslint nomen: true*/
/*global define*/
define(function (require) {
    'use strict';

    var $ = require('jquery'),
        mediator = require('oroui/js/mediator');

    return function (options) {
        var $btn = options._sourceElement,
            message = $btn.data('message'),
            url = $btn.data('url');

        $btn.on('click', function () {
            $.post(url, {status: options.status}).done(function() {
                if (message) {
                    mediator.execute('addMessage', 'success', message);
                }
                mediator.execute('refreshPage');
            });
        });
    };
});
