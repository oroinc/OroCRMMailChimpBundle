<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\MailChimpBundle\Transport\MailChimpTransport;

class CampaignDataConverter extends IntegrationAwareDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            // MailChimp campaign fields
            'id' => 'originId',
            'web_id' => 'webId',
            'title' => 'title',
            'subject' => 'subject',
            'type' => 'type',
            'template_id' => 'template:originId',
            'list_id' => 'subscribersList:originId',
            'content_type' => 'contentType',
            'create_time' => 'createdAt',
            'content_updated_time' => 'updatedAt',
            'archive_url' => 'archiveUrl',
            'archive_url_long' => 'archiveUrlLong',
            'tests_sent' => 'testsSent',
            'tests_remain' => 'testsRemain',

            // Email campaign related
            'send_time' => 'sendTime',
            'from_name' => 'fromName',
            'from_email' => 'fromEmail',

            // MailChimp campaign Summary
            'last_open' => 'lastOpenDate',
            'syntax_errors' => 'syntaxErrors',
            'hard_bounces' => 'hardBounces',
            'soft_bounces' => 'softBounces',
            'abuse_reports' => 'abuseReports',
            'forwards_opens' => 'forwardsOpens',
            'unique_opens' => 'uniqueOpens',
            'unique_clicks' => 'uniqueClicks',
            'users_who_clicked' => 'usersWhoClicked',
            'unique_likes' => 'uniqueLikes',
            'recipient_likes' => 'recipientLikes',
            'facebook_likes' => 'facebookLikes',
            'emails_sent' => 'emailsSent',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        if (is_array($importedRecord['summary'])) {
            $importedRecord = array_merge($importedRecord, $importedRecord['summary']);
            unset($importedRecord['summary']);
        }
        $channel = $this->context->getOption('channel');
        $importedRecord['template:channel:id'] = $channel;
        $importedRecord['subscribersList:channel:id'] = $channel;
        if (isset($importedRecord['saved_segment'], $importedRecord['saved_segment']['id'])) {
            $importedRecord['segment:originId'] = $importedRecord['saved_segment']['id'];
            $importedRecord['segment:channel:id'] = $channel;
        }

        return parent::convertToImportFormat($importedRecord, $skipNullValues);
    }


    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        throw new \Exception('Normalization is not implemented!');
    }
}
