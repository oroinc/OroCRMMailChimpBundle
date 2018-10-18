<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\DataConverter;

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
            'use_archive_bar' => 'useAwesomeBar',
            'from_name' => 'defaultFromName',
            'from_email' => 'defaultFromEmail',
            'subject' => 'defaultSubject',
            'language' => 'defaultLanguage',
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
            'merge_field_count' => 'mergeVarCount',
            'avg_sub_rate' => 'avgSubRate',
            'avg_unsub_rate' => 'avgUsubRate',
            'target_sub_rate' => 'targetSubRate',
            'open_rate' => 'openRate',
            'click_rate' => 'clickRate',
            'merge_fields' => 'mergeVarConfig',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        if (array_key_exists('_links', $importedRecord)) {
            unset($importedRecord['_links']);
        }

        if (array_key_exists('merge_fields', $importedRecord) && is_array($importedRecord['merge_fields'])) {
            foreach ($importedRecord['merge_fields'] as $key => $merge_field) {
                if (array_key_exists('_links', $merge_field)) {
                    unset($merge_field['_links']);
                }
                $importedRecord['merge_fields'][$key] = $merge_field;
            }
        }

        if (is_array($importedRecord['stats'])) {
            $importedRecord = array_merge($importedRecord, $importedRecord['stats']);
            unset($importedRecord['stats']);
        }

        if (is_array($importedRecord['campaign_defaults'])) {
            $importedRecord = array_merge($importedRecord, $importedRecord['campaign_defaults']);
            unset($importedRecord['campaign_defaults']);
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
