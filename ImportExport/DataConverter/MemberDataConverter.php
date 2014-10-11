<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

class MemberDataConverter extends IntegrationAwareDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'LEID' => 'originId',
            'status' => 'status',
            'list_id' => 'subscribersList:originId',
            'Email Address' => 'email',
            'First Name' => 'firstName',
            'Last Name' => 'lastName',
            'Company' => 'company',
            'MEMBER_RATING' => 'memberRating',
            'OPTIN_TIME' => 'optedInAt',
            'OPTIN_IP' => 'optedInIpAddress',
            'CONFIRM_TIME' => 'confirmedAt',
            'CONFIRM_IP' => 'confirmedIpAddress',
            'LATITUDE' => 'latitude',
            'LONGITUDE' => 'longitude',
            'GMTOFF' => 'gtmOffset',
            'DSTOFF' => 'dstOffset',
            'TIMEZONE' => 'timezone',
            'CC' => 'cc',
            'REGION' => 'region',
            'LAST_CHANGED' => 'lastChangedAt',
            'EUID' => 'euid',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        throw new \Exception('Normalization is not implemented!');
    }
}
