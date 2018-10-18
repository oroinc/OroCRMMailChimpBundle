define(function(require) {
    'use strict';

    var _ = require('underscore');
    var __ = require('orotranslation/js/translator');
    var $ = require('jquery');

    return function(options) {
        var $source = options._sourceElement;
        var $apiKeyEl = $source.find('input');
        var $btn = $source.find('button');
        var $status = $source.find('.connection-status');
        var $pingHolder = $source.find('.ping-holder');

        var onError = function(message) {
            message = message || __('oro.mailchimp.integration_transport.api_key.check.message');
            $status.removeClass('alert-info')
                .addClass('alert-error')
                .html(message)
                .show();
        };

        var localCheckApiKey = function() {
            if ($apiKeyEl.val().length) {
                $pingHolder.show();
            } else {
                $pingHolder.hide();
            }
        };

        localCheckApiKey();
        $apiKeyEl.on('keyup', function() {
            localCheckApiKey();
        });

        $btn.on('click', function(e) {
            e.preventDefault();
            if ($apiKeyEl.valid()) {
                $.getJSON(options.pingUrl, {api_key: $apiKeyEl.val()})
                    .then(function(response) {
                        if (_.isUndefined(response.error)) {
                            $status.removeClass('alert-error')
                                .addClass('alert-info')
                                .html(response.msg)
                                .show();
                        } else {
                            onError(response.error);
                        }
                    })
                    .catch(function(response) {
                        onError(response.responseJSON.error);
                    });

                return;
            }
            onError();
        });
    };
});
