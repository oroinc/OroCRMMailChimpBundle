<?php

namespace Oro\Bundle\MailChimpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="orocrm_mailchimp_ml_email",
 *      indexes={
 *          @ORM\Index(name="mc_ml_email_idx", columns={"email"}),
 *          @ORM\Index(name="mc_ml_email_state_idx", columns={"state"})
 *      },
 * )
 */
class MarketingListEmail
{
    const STATE_IN_LIST = 'in_list';

    /**
     * @var MarketingList
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MarketingListBundle\Entity\MarketingList")
     * @ORM\JoinColumn(name="marketing_list_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $marketingList;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(name="state", type="string", length=25, nullable=false)
     */
    protected $state = self::STATE_IN_LIST;

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     * @return MarketingListEmail
     */
    public function setState($state)
    {
        $this->state = $state;

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
     * @return MarketingListEmail
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return MarketingList
     */
    public function getMarketingList()
    {
        return $this->marketingList;
    }

    /**
     * @param MarketingList $marketingList
     * @return MarketingListEmail
     */
    public function setMarketingList(MarketingList $marketingList)
    {
        $this->marketingList = $marketingList;

        return $this;
    }
}
