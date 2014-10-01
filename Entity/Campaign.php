<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *      name="orocrm_mailchimp_campaign",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="mc_campaign_oid_cid_unq", columns={"origin_id", "channel_id"})
 *     }
 * )
 * @Config
 */
class Campaign
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(name="origin_id", type="bigint", nullable=false)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "identity"=true
     *          }
     *      }
     * )
     */
    protected $originId;

    /**
     * @var Channel
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\IntegrationBundle\Entity\Channel")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "identity"=true
     *          }
     *      }
     * )
     */
    protected $channel;

    /**
     * @var EmailCampaign
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign")
     * @ORM\JoinColumn(name="email_campaign_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "identity"=true
     *          }
     *      }
     * )
     */
//    protected $emailCampaign;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set originId
     *
     * @param integer $originId
     * @return Campaign
     */
    public function setOriginId($originId)
    {
        $this->originId = $originId;

        return $this;
    }

    /**
     * Get originId
     *
     * @return integer 
     */
    public function getOriginId()
    {
        return $this->originId;
    }

    /**
     * @param Channel $integration
     * @return Campaign
     */
    public function setChannel(Channel $integration)
    {
        $this->channel = $integration;

        return $this;
    }

    /**
     * @return Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return EmailCampaign
     */
    public function getEmailCampaign()
    {
        return $this->emailCampaign;
    }

    /**
     * @param EmailCampaign $emailCampaign
     * @return Campaign
     */
    public function setEmailCampaign(EmailCampaign $emailCampaign)
    {
        $this->emailCampaign = $emailCampaign;

        return $this;
    }
}
