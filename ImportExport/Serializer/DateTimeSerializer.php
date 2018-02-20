<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\Serializer;

use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DateTimeNormalizer as BaseNormalizer;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DenormalizerInterface;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\NormalizerInterface;
use Oro\Bundle\MailChimpBundle\Provider\ChannelType;
use Oro\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;
use Symfony\Component\Serializer\Exception\RuntimeException;

class DateTimeSerializer implements NormalizerInterface, DenormalizerInterface
{
    const CHANNEL_TYPE_KEY = 'channelType';

    public function __construct()
    {
        $this->mailchimpNormalizer = new BaseNormalizer(
            MailChimpTransport::DATETIME_FORMAT,
            MailChimpTransport::DATE_FORMAT,
            MailChimpTransport::TIME_FORMAT,
            MailChimpTransport::TIMEZONE
        );
        $this->isoNormalizer = new BaseNormalizer(\DateTime::ISO8601, 'Y-m-d', 'H:i:s', 'UTC');
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        try {
            return $this->mailchimpNormalizer->denormalize($data, $class, $format, $context);
        } catch (RuntimeException $e) {
            return $this->isoNormalizer->denormalize($data, $class, $format, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return $this->mailchimpNormalizer->normalize($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return $this->mailchimpNormalizer->supportsDenormalization($data, $type, $format, $context)
            && !empty($context[self::CHANNEL_TYPE_KEY])
            && strpos($context[self::CHANNEL_TYPE_KEY], ChannelType::TYPE) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return $this->mailchimpNormalizer->supportsNormalization($data, $format, $context)
            && !empty($context[self::CHANNEL_TYPE_KEY])
            && strpos($context[self::CHANNEL_TYPE_KEY], ChannelType::TYPE) !== false;
    }
}
