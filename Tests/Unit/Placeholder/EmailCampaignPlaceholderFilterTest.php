<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Placeholder;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\MailChimpBundle\Entity\Campaign;
use Oro\Bundle\MailChimpBundle\Placeholder\EmailCampaignPlaceholderFilter;

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
     * @var EmailCampaignPlaceholderFilter
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
        $this->managerRegistry = $this->createMock('Doctrine\Common\Persistence\ManagerRegistry');

        $this->placeholderFilter = new EmailCampaignPlaceholderFilter($this->managerRegistry);
    }

    public function testIsNotApplicableEntityOnEmailCampaign()
    {
        $entity = $this->createMock('Oro\Bundle\MarketingListBundle\Entity\MarketingList');
        $this->assertFalse($this->placeholderFilter->isApplicableOnEmailCampaign($entity));
    }

    /**
     * @param EmailCampaign $emailCampaign
     * @param Campaign $campaign
     * @param bool $expected
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

        $this->assertEquals(
            $expected,
            $this->placeholderFilter->isApplicableOnEmailCampaign($emailCampaign)
        );
    }

    /**
     * @return array
     */
    public function staticCampaignProvider()
    {
        $emailCampaign = new EmailCampaign();
        $mailchimpEmailCampaign = new EmailCampaign();
        $mailchimpEmailCampaign->setTransport('mailchimp');
        return [
            [null, null, false],
            [null, new Campaign(), false],
            [$emailCampaign, null, false],
            [$emailCampaign, new Campaign(), false],
            [$mailchimpEmailCampaign, null, false],
            [$mailchimpEmailCampaign, new Campaign(), true],
        ];
    }
}
