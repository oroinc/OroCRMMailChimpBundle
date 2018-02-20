<?php

namespace Oro\Bundle\MailChimpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @ORM\Entity()
 */
class MailChimpTransport extends Transport
{
    const DEFAULT_ACTIVITY_UPDATE_INTERVAL = 90;

    /**
     * @var string
     *
     * @ORM\Column(name="orocrm_mailchimp_apikey", type="string", length=255, nullable=false)
     */
    protected $apiKey;

    /**
     * Activity update interval after send date. Days.
     *
     * @var int
     *
     * @ORM\Column(name="orocrm_mailchimp_act_up_int", type="integer", nullable=true)
     */
    protected $activityUpdateInterval = self::DEFAULT_ACTIVITY_UPDATE_INTERVAL;

    /**
     * @var ParameterBag
     */
    protected $settingsBag;

    /**
     * {@inheritdoc}
     */
    public function getSettingsBag()
    {
        if (null === $this->settingsBag) {
            $this->settingsBag = new ParameterBag(
                [
                    'apiKey' => $this->getApiKey(),
                    'activityUpdateInterval' => $this->getActivityUpdateInterval()
                ]
            );
        }

        return $this->settingsBag;
    }

    /**
     * Set apiKey
     *
     * @param string $apiKey
     * @return MailChimpTransport
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get apiKey
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @return int
     */
    public function getActivityUpdateInterval()
    {
        return $this->activityUpdateInterval;
    }

    /**
     * @param int $activityUpdateInterval
     * @return MailChimpTransport
     */
    public function setActivityUpdateInterval($activityUpdateInterval)
    {
        $this->activityUpdateInterval = $activityUpdateInterval;

        return $this;
    }
}
