<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType;

class ListDataConverter extends IntegrationAwareDataConverter
{
    // TODO: Replace this with OroCRM\Bundle\MailChimpBundle\Entity\Subscriber item when it will be created
    const MARKETING_LIST_TARGET_ENTITY = 'OroCRM\Bundle\ContactBundle\Entity\Contact';

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
        $channel = $this->context->getOption('channel');

        $importedRecord['marketingList:channel:id'] = $channel;
        $importedRecord['marketingList:name'] = $importedRecord['name'];
        $importedRecord['marketingList:entity'] = self::MARKETING_LIST_TARGET_ENTITY;
        $importedRecord['marketingList:type:name'] = MarketingListType::TYPE_MANUAL;

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
