<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Serializer;

use Symfony\Component\Serializer\Exception\RuntimeException;

use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DateTimeNormalizer as BaseNormalizer;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DenormalizerInterface;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\NormalizerInterface;
use OroCRM\Bundle\MailChimpBundle\Provider\ChannelType;

class DateTimeSerializer implements NormalizerInterface, DenormalizerInterface
{
    const CHANNEL_TYPE_KEY = 'channelType';

    public function __construct()
    {
        $this->magentoNormalizer = new BaseNormalizer('Y-m-d H:i:s', 'Y-m-d', 'H:i:s', 'UTC');
        $this->isoNormalizer = new BaseNormalizer(\DateTime::ISO8601, 'Y-m-d', 'H:i:s', 'UTC');
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        try {
            return $this->magentoNormalizer->denormalize($data, $class, $format, $context);
        } catch (RuntimeException $e) {
            return $this->isoNormalizer->denormalize($data, $class, $format, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return $this->magentoNormalizer->normalize($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        return $this->magentoNormalizer->supportsDenormalization($data, $type, $format, $context)
            && !empty($context[self::CHANNEL_TYPE_KEY])
            && strpos($context[self::CHANNEL_TYPE_KEY], ChannelType::TYPE) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return $this->magentoNormalizer->supportsNormalization($data, $format, $context)
            && !empty($context[self::CHANNEL_TYPE_KEY])
            && strpos($context[self::CHANNEL_TYPE_KEY], ChannelType::TYPE) !== false;
    }
}
