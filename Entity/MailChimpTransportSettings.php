<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\ParameterBag;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use OroCRM\Bundle\CampaignBundle\Entity\TransportSettings;

/**
 * @ORM\Entity
 * @Config()
 */
class MailChimpTransportSettings extends TransportSettings
{
    /**
     * @var Channel
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\IntegrationBundle\Entity\Channel")
     * @ORM\JoinColumn(name="mailchimp_channel_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $channel;

    /**
     * @var Template
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MailChimpBundle\Entity\Template")
     * @ORM\JoinColumn(name="mailchimp_template_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $template;

    /**
     * @return Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param Channel $channel
     * @return MailChimpTransportSettings
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Set template
     *
     * @param Template $emailTemplate
     *
     * @return MailChimpTransportSettings
     */
    public function setTemplate(Template $emailTemplate = null)
    {
        $this->template = $emailTemplate;

        return $this;
    }

    /**
     * Get template
     *
     * @return Template|null
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsBag()
    {
        if (null === $this->settings) {
            $this->settings = new ParameterBag(
                [
                    'channel' => $this->getChannel(),
                    // 'template' => $this->getTemplate()
                ]
            );
        }

        return $this->settings;
    }
}
