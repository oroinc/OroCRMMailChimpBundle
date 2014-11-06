<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Placeholder;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ManagerRegistry;

use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\MailChimpBundle\Entity\Campaign;
use OroCRM\Bundle\MailChimpBundle\Placeholder\EmailCampaignPlaceholderFilter;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class EmailCampaignPlaceholderFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $managerRegistry;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityRepository;

    /**
     * @var PlaceholderFilter
     */
    protected $placeholderFilter;

    protected function setUp()
    {
        $this->entityRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('findOneBy'))
            ->getMock();
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getRepository'))
            ->getMock();
        $this->managerRegistry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');

        $this->placeholderFilter = new EmailCampaignPlaceholderFilter($this->managerRegistry);
    }

    public function testIsNotApplicableEntityOnEmailCampaign()
    {
        $entity = $this->getMock('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList');
        $this->assertFalse($this->placeholderFilter->isApplicableOnEmailCampaign($entity));
    }

    /**
     * @param $emailCampaign
     * @param $campaign
     * @param $expected
     * @dataProvider staticCampaignProvider
     */
    public function testIsApplicableOnEmailCampaign($emailCampaign, $campaign, $expected)
    {
        $this->entityRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($campaign));
        $this->managerRegistry->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->entityManager));
        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->entityRepository));


        $this->assertEquals($expected,
            $this->placeholderFilter->isApplicableOnEmailCampaign($emailCampaign));
    }

    /**
     * @return array
     */
    public function staticCampaignProvider()
    {
        return [
            [null, null, false],
            [null, new Campaign(), false],
            [new EmailCampaign(), null, false],
            [new EmailCampaign(), new Campaign(), false],
            [(new EmailCampaign())->setTransport('mailchimp'), null, false],
            [(new EmailCampaign())->setTransport('mailchimp'), new Campaign(), true],
        ];
    }
}
