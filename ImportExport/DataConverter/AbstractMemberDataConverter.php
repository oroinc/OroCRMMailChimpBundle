<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\DataConverter;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Validation;

abstract class AbstractMemberDataConverter extends IntegrationAwareDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'status' => 'status',
            'list_id' => 'subscribersList:originId',
            'channel_id' => 'channel:id',
            'subscribersList_id' => 'subscribersList:id',
            'email' => 'email',
            'origin_id' => 'originId',
            'MEMBER_RATING' => 'memberRating',
            'OPTIN_TIME' => 'optedInAt',
            'OPTIN_IP' => 'optedInIpAddress',
            'CONFIRM_TIME' => 'confirmedAt',
            'CONFIRM_IP' => 'confirmedIpAddress',
            'LATITUDE' => 'latitude',
            'LONGITUDE' => 'longitude',
            'GMTOFF' => 'gmtOffset',
            'DSTOFF' => 'dstOffset',
            'TIMEZONE' => 'timezone',
            'CC' => 'cc',
            'REGION' => 'region',
            'LAST_CHANGED' => 'lastChangedAt',
            'EUID' => 'euid',
            'LEID' => 'leid',
            'NOTES' => 'notes',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        reset($importedRecord);
        $email = current($importedRecord);

        $validator = Validation::createValidator();
        $emailViolations = $validator->validate($email, [new Email(), new NotNull(), new NotBlank()]);
        if (count($emailViolations) === 0) {
            $importedRecord['email'] = $email;
            $importedRecord['origin_id'] = md5(strtolower($email));
        }

        if ($this->context->hasOption('channel')) {
            $channel = $this->context->getOption('channel');
            $importedRecord['subscribersList:channel:id'] = $channel;
        }

        $mergeVarValues = [];

        foreach ($importedRecord as $key => $value) {
            if ($this->isMergeVarValueColumn($key)) {
                $mergeVarValues[$key] = $value;
                unset($importedRecord[$key]);
            }
        }

        $importedRecord['mergeVarValues'] = $mergeVarValues;

        return parent::convertToImportFormat($importedRecord, $skipNullValues);
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function isMergeVarValueColumn($name)
    {
        $headerConversionRules = $this->getHeaderConversionRules();

        return !isset($headerConversionRules[$name]) && $name !== 'subscribersList:channel:id';
    }

    /**
     * {@inheritdoc}
     */
    protected function getBackendHeader()
    {
        throw new \Exception('Normalization is not implemented!');
    }
}
