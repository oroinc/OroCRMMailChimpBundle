<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
 * @link http://apidocs.mailchimp.com/api/2.0/lists/member-activity.php
 *
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\MailChimpBundle\Entity\Repository\MemberActivityRepository")
 * @ORM\Table(
 *      name="orocrm_mc_mmbr_activity",
 *      indexes={
 *          @ORM\Index(name="mc_mmbr_activity_action_idx", columns={"action"})
 *      },
 * )
 * @Config(
 *  defaultValues={
 *      "entity"={
 *          "icon"="icon-bar-chart"
 *      },
 *      "ownership"={
 *          "owner_type"="ORGANIZATION",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="owner_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 */
class MemberActivity
{
    /**#@+
     * @const string Activity of Member Activity
     */
    const ACTIVITY_OPEN = 'open';
    const ACTIVITY_CLICK = 'click';
    const ACTIVITY_BOUNCE = 'bounce';
    const ACTIVITY_UNSUB = 'unsub';
    const ACTIVITY_ABUSE = 'abuse';
    const ACTIVITY_SENT = 'sent';
    const ACTIVITY_ECOMM = 'ecomm';
    const ACTIVITY_MANDRILL_SEND = 'mandrill_send';
    const ACTIVITY_MANDRILL_HARD_BOUNCE = 'mandrill_hard_bounce';
    const ACTIVITY_MANDRILL_SOFT_BOUNCE = 'mandrill_soft_bounce';
    const ACTIVITY_MANDRILL_OPEN = 'mandrill_open';
    const ACTIVITY_MANDRILL_CLICK = 'mandrill_click';
    const ACTIVITY_MANDRILL_SPAM = 'mandrill_spam';
    const ACTIVITY_MANDRILL_UNSUB = 'mandrill_unsub';
    const ACTIVITY_MANDRILL_REJECT = 'mandrill_reject';
    /**#@-*/

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Channel
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\IntegrationBundle\Entity\Channel")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $channel;

    /**
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MailChimpBundle\Entity\Campaign")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $campaign;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MailChimpBundle\Entity\Member")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $member;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=25, nullable=false)
     */
    protected $action;

    /**
     * @var string
     *
     * @ORM\Column(name="ip_address", type="string", length=45, nullable=true)
     */
    protected $ip;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="activity_time", type="datetime", nullable=true)
     */
    protected $activityTime;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="text", nullable=true)
     */
    protected $url;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param Channel $channel
     * @return MemberActivity
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @param Campaign $campaign
     * @return MemberActivity
     */
    public function setCampaign($campaign)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @param Member $member
     * @return MemberActivity
     */
    public function setMember($member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return MemberActivity
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     * @return MemberActivity
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     * @return MemberActivity
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getActivityTime()
    {
        return $this->activityTime;
    }

    /**
     * @param \DateTime $activityTime
     * @return MemberActivity
     */
    public function setActivityTime($activityTime)
    {
        $this->activityTime = $activityTime;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return MemberActivity
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return Organization
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param Organization $owner
     * @return MemberActivity
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }
}
