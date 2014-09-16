/*jslint nomen: true*/
/*global define*/
define(function (require) {
    'use strict';

    var _ = require('underscore'),
        __ = require('orotranslation/js/translator'),
        $ = require('jquery');

    return function (options) {
        var $source = options._sourceElement,
            $apiKeyEl = $source.find('input'),
            $btn = $source.find('button'),
            $status = $source.find('.connection-status'),
            $pingHolder = $source.find('.ping-holder');

        var onError = function (message) {
            message = message || __('orocrm.mailchimp.integration_transport.api_key.check.message');
            $status.removeClass('alert-info')
                .addClass('alert-error')
                .html(message)
        };

        var localCheckApiKey = function () {
            if ($apiKeyEl.val().length) {
                $pingHolder.show();
            } else {
                $pingHolder.hide();
            }
        };

        localCheckApiKey();
        $apiKeyEl.on('keyup', function () {
            localCheckApiKey();
        });

        $btn.on('click', function () {
            if ($apiKeyEl.valid()) {
                $.getJSON(
                    options.pingUrl,
                    {'api_key': $apiKeyEl.val()},
                    function (response) {
                        if (_.isUndefined(response.error)) {
                            $status.removeClass('alert-error')
                                .addClass('alert-info')
                                .html(response.msg);
                        } else {
                            onError(response.error);
                        }
                    }
                ).always(
                    function () {
                        $status.show();
                    }
                ).fail(
                    onError
                );
            } else {
                $status.show();
                onError();
            }
        });
    };
});
