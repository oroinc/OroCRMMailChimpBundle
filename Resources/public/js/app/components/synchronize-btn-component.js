define(function(require) {
    'use strict';

    var $ = require('jquery');
    var mediator = require('oroui/js/mediator');

    return function(options) {
        var $btn = options._sourceElement;
        var message = $btn.data('message');
        var url = $btn.data('url');

        $btn.on('click', function() {
            $.post(url, {status: options.status}).done(function() {
                if (message) {
                    mediator.execute('addMessage', 'success', message);
                }
                mediator.execute('refreshPage');
            });
        });
    };
});
