<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Serializer;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DenormalizerInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;
use OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter\MemberDataConverter;

/**
 * Added during performance improvement. Please, keep it as simple as possible.
 * Used for batch importing of members from MailChimp, may process significant amount of records.
 */
class MemberImportSerializer implements DenormalizerInterface
{
    /**
     * @var DateTimeSerializer
     */
    protected $dateTimeSerializer;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var string
     */
    protected $channelEntity;

    /**
     * @param string $channelEntity
     * @return MemberImportSerializer
     */
    public function setChannelEntity($channelEntity)
    {
        $this->channelEntity = $channelEntity;

        return $this;
    }

    /**
     * @param DoctrineHelper $doctrineHelper
     * @return MemberImportSerializer
     */
    public function setDoctrineHelper(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;

        return $this;
    }

    /**
     * @param DateTimeSerializer $dateTimeSerializer
     * @return MemberImportSerializer
     */
    public function setDateTimeSerializer(DateTimeSerializer $dateTimeSerializer)
    {
        $this->dateTimeSerializer = $dateTimeSerializer;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $result = new Member();
        // Scalar fields
        if (array_key_exists('originId', $data)) {
            $result->setOriginId($data['originId']);
        }
        if (array_key_exists('status', $data)) {
            $result->setStatus($data['status']);
        }
        if (array_key_exists('memberRating', $data)) {
            $result->setMemberRating($data['memberRating']);
        }
        if (array_key_exists('optedInIpAddress', $data)) {
            $result->setOptedInIpAddress($data['optedInIpAddress']);
        }
        if (array_key_exists('confirmedIpAddress', $data)) {
            $result->setConfirmedIpAddress($data['confirmedIpAddress']);
        }
        if (array_key_exists('latitude', $data)) {
            $result->setLatitude($data['latitude']);
        }
        if (array_key_exists('longitude', $data)) {
            $result->setLongitude($data['longitude']);
        }
        if (array_key_exists('dstOffset', $data)) {
            $result->setDstOffset($data['dstOffset']);
        }
        if (array_key_exists('gmtOffset', $data)) {
            $result->setGmtOffset($data['gmtOffset']);
        }
        if (array_key_exists('timezone', $data)) {
            $result->setTimezone($data['timezone']);
        }
        if (array_key_exists('cc', $data)) {
            $result->setCc($data['cc']);
        }
        if (array_key_exists('region', $data)) {
            $result->setRegion($data['region']);
        }
        if (array_key_exists('euid', $data)) {
            $result->setEuid($data['euid']);
        }
        if (array_key_exists('mergeVarValues', $data)) {
            $result->setMergeVarValues($data['mergeVarValues']);
        }

        // DateTime fields
        if (array_key_exists('optedInAt', $data)) {
            $result->setOptedInAt($this->getDateTime($data['optedInAt'], $context));
        }
        if (!empty($data['confirmedAt'])) {
            $result->setConfirmedAt($this->getDateTime($data['confirmedAt'], $context));
        }
        if (!empty($data['lastChangedAt'])) {
            $result->setLastChangedAt($this->getDateTime($data['lastChangedAt'], $context));
        }

        // Relations
        /** @var Channel $channel */
        $channel = $this->doctrineHelper->getEntityReference($this->channelEntity, $context['channel']);
        $result->setChannel($channel);

        $subscribersList = null;
        if (!empty($data['subscribersList']['originId'])) {
            $subscribersList = new SubscribersList();
            $subscribersList->setChannel($channel);
            $subscribersList->setOriginId($data['subscribersList']['originId']);
        } elseif (!empty($data['subscribersList']['id'])) {
            $this->doctrineHelper->getEntityReference(
                'OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList',
                $context['channel']
            );
        }

        if ($subscribersList) {
            $result->setSubscribersList($subscribersList);
        }

        return $result;
    }

    /**
     * @param string $dateString
     * @param array $context
     * @return \DateTime|null
     */
    protected function getDateTime($dateString, array $context = [])
    {
        return $this->dateTimeSerializer->denormalize(
            $dateString,
            'DateTime',
            'datetime',
            $context
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return is_array($data)
            && array_key_exists(MemberDataConverter::IMPORT_DATA, $data)
            && !empty($context['channel'])
            && is_a($type, 'OroCRM\Bundle\MailChimpBundle\Entity\Member', true);
    }
}
