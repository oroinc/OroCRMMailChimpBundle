/*jslint nomen: true*/
/*global define*/
define(function (require) {
    'use strict';

    var $ = require('jquery'),
        mediator = require('oroui/js/mediator');

    return function (options) {
        options._sourceElement.click(function(e) {
            var url = $(e.target).data('url');
            e.preventDefault();

            mediator.execute('showLoading');
            $.post(url, function (response) {
                mediator.once("page:update", function () {
                    mediator.execute('showFlashMessage', 'success', response.message);
                });
                mediator.execute('refreshPage');
            }).error(function () {
                mediator.execute('showFlashMessage', 'error', __('orocrm.mailchimp.request.error'));
            }).always(function () {
                mediator.execute('hideLoading');
            });
        });
    };
});
