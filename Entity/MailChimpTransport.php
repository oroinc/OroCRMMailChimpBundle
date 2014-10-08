<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\ParameterBag;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\IntegrationBundle\Entity\Transport;

/**
 * @ORM\Entity()
 * @Config(
 *      defaultValues={
 *          "note"={
 *              "immutable"=true
 *          },
 *          "activity"={
 *              "immutable"=true
 *          },
 *          "attachment"={
 *              "immutable"=true
 *          }
 *      }
 * )
 */
class MailChimpTransport extends Transport
{
    /**
     * @var string
     *
     * @ORM\Column(name="orocrm_mailchimp_apikey", type="string", length=255, nullable=false)
     */
    protected $apiKey;

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
                    'apiKey' => $this->getApiKey()
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
}
