/*jslint nomen: true*/
/*global define*/
define(function(require) {
    'use strict';

    var $ = require('jquery');
    var mediator = require('oroui/js/mediator');
    var __ = require('orotranslation/js/translator');

    return function(options) {
        options._sourceElement.click(function(e) {
            var url = $(e.target).data('url');
            e.preventDefault();

            mediator.execute('showLoading');
            $.post({
                url: url,
                errorHandlerMessage: __('oro.mailchimp.request.error')
            }).done(function(response) {
                mediator.once('page:update', function() {
                    mediator.execute('showFlashMessage', 'success', response.message);
                });
                mediator.execute('refreshPage');
            }).always(function() {
                mediator.execute('hideLoading');
            });
        });
    };
});
