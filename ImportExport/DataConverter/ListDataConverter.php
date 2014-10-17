<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

class ListDataConverter extends IntegrationAwareDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'id' => 'originId',
            'web_id' => 'webId',
            'date_created' => 'createdAt',
            'email_type_option' => 'emailTypeOption',
            'use_awesomebar' => 'useAwesomeBar',
            'default_from_name' => 'defaultFromName',
            'default_from_email' => 'defaultFromEmail',
            'default_subject' => 'defaultSubject',
            'default_language' => 'defaultLanguage',
            'list_rating' => 'listRating',
            'subscribe_url_short' => 'subscribeUrlShort',
            'subscribe_url_long' => 'subscribeUrlLong',
            'beamer_address' => 'beamerAddress',
            'member_count' => 'memberCount',
            'unsubscribe_count' => 'unsubscribeCount',
            'cleaned_count' => 'cleanedCount',
            'member_count_since_send' => 'memberCountSinceSend',
            'unsubscribe_count_since_send' => 'unsubscribeCountSinceSend',
            'cleaned_count_since_send' => 'cleanedCountSinceSend',
            'campaign_count' => 'campaignCount',
            'grouping_count' => 'groupingCount',
            'group_count' => 'groupCount',
            'merge_var_count' => 'mergeVarCount',
            'avg_sub_rate' => 'avgSubRate',
            'avg_unsub_rate' => 'avgUsubRate',
            'target_sub_rate' => 'targetSubRate',
            'open_rate' => 'openRate',
            'click_rate' => 'clickRate',
            'merge_vars' => 'mergeVarConfig',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        if (is_array($importedRecord['stats'])) {
            $importedRecord = array_merge($importedRecord, $importedRecord['stats']);
            unset($importedRecord['stats']);
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
