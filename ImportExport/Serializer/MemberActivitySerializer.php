<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Serializer;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DenormalizerInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Entity\MemberActivity;

/**
 * Added during performance improvement. Please, keep it as simple as possible.
 * Used for batch importing of member activities from MailChimp, may process significant amount of records.
 */
class MemberActivitySerializer implements DenormalizerInterface
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
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $result = new MemberActivity();
        // Scalar fields
        if (array_key_exists('email', $data)) {
            $result->setEmail($data['email']);
        }
        if (array_key_exists('action', $data)) {
            $result->setAction($data['action']);
        }
        if (array_key_exists('ip', $data)) {
            $result->setIp($data['ip']);
        }
        if (array_key_exists('url', $data)) {
            $result->setUrl($data['url']);
        }

        // DateTime fields
        if (!empty($data['activityTime'])) {
            $result->setActivityTime($this->getDateTime($data['activityTime'], $context));
        }

        // Relations
        /** @var Channel $channel */
        $channel = $this->doctrineHelper->getEntityReference($this->channelEntity, $context['channel']);
        $result->setChannel($channel);

        if (array_key_exists('campaign', $data)) {
            $result->setCampaign($data['campaign']);
            $result->getCampaign()->setChannel($channel);
        }

        if (array_key_exists('member', $data)) {
            $member = new Member();
            if (!empty($data['member']['originId'])) {
                $member->setOriginId($data['member']['originId']);
            }
            if (!empty($data['member']['email'])) {
                $member->setEmail($data['member']['email']);
            }
            $member->setChannel($channel);
            $result->setMember($member);
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
        return is_a($type, 'OroCRM\Bundle\MailChimpBundle\Entity\MemberActivity', true)
            && is_array($data)
            && !empty($context['channel']);
    }
}
