<?php

namespace OroCRM\Bundle\MailChimpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="orocrm_mailchimp_ml_email",
 *      indexes={
 *          @ORM\Index(name="mc_ml_email_idx", columns={"email"})
 *      },
 * )
 */
class MarketingListEmail
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
     * @var MarketingList
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MarketingListBundle\Entity\MarketingList")
     * @ORM\JoinColumn(name="marketing_list_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $marketingList;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    protected $email;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
