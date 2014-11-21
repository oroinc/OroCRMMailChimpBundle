<?php

return [
    'GetCampaignUnsubscribesReport' => [
        'httpMethod' => 'POST',
        'uri' => 'reports/unsubscribes.json',
        'summary' => 'Get all unsubscribed email addresses for a given campaign',
        'documentationUrl' => 'http://apidocs.mailchimp.com/api/2.0/reports/unsubscribes.php',
        'parameters' => [
            'api_key' => [
                'description' => 'MailChimp API key',
                'location' => 'json',
                'type' => 'string',
                'sentAs' => 'apikey',
                'required' => true
            ],
            'cid' => [
                'description' => 'Campaign id to pull abuse report for',
                'location' => 'json',
                'type' => 'string',
                'required' => true
            ],
            'opts' => [
                'description' => 'Optional options to control returned data',
                'location' => 'json',
                'type' => 'array',
                'required' => false
            ]
        ]
    ],

    'GetCampaignSentToReport' => [
        'httpMethod' => 'POST',
        'uri' => 'reports/sent-to.json',
        'summary' => 'Get email addresses the campaign was sent to',
        'documentationUrl' => 'http://apidocs.mailchimp.com/api/2.0/reports/sent-to.php',
        'parameters' => [
            'api_key' => [
                'description' => 'MailChimp API key',
                'location' => 'json',
                'type' => 'string',
                'sentAs' => 'apikey',
                'required' => true
            ],
            'cid' => [
                'description' => 'Campaign id to pull abuse report for',
                'location' => 'json',
                'type' => 'string',
                'required' => true
            ],
            'opts' => [
                'description' => 'Optional options to control returned data',
                'location' => 'json',
                'type' => 'array',
                'required' => false
            ]
        ]
    ]
];
