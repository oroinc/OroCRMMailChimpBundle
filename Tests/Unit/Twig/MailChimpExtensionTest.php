<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Twig;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ManagerRegistry;

use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Twig\MailChimpExtension;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class MailChimpExtensiontTest extends \PHPUnit_Framework_TestCase
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
     * @var MailChimpExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->entityRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('findOneBy'))
            ->getMock();
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getRepository', 'getMetadataFactory'))
            ->getMock();
        $this->managerRegistry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');

        $this->extension = new MailChimpExtension($this->managerRegistry);
    }

    public function testGetFunctions()
    {
        $functions = array(
            new \Twig_SimpleFunction(
                'orocrm_mailchimp_email_campaign',
                [$this->extension, 'getEmailCampaign']
            ),
            new \Twig_SimpleFunction(
                'orocrm_mailchimp_email_campaign_sync_status',
                [$this->extension, 'getEmailCampaignSyncStatus']
            ),
        );
        $this->assertEquals($functions, $this->extension->getFunctions());
    }

    public function testCorrectName()
    {
        $name = 'orocrm_mailchimp';
        $this->assertEquals($name, $this->extension->getName());
    }

    public function testGetEmailCampaign()
    {
        $staticSegment = new StaticSegment();
        $this->entityRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue($staticSegment));
        $this->managerRegistry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($this->entityManager));
        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($this->entityRepository));

        $entity = new MarketingList();
        $this->assertEquals($staticSegment, $this->extension->getEmailCampaign($entity));
    }

    public function testIsSyncedSegment()
    {
        $staticSegment = new StaticSegment();
        $staticSegment->setSyncStatus(StaticSegment::STATUS_SYNCED);
        $this->entityRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue($staticSegment));
        $this->managerRegistry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($this->entityManager));
        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($this->entityRepository));

        $entity = new MarketingList();
        $this->assertEquals(true, $this->extension->getEmailCampaignSyncStatus($entity));
    }

    public function testIsNotSyncedSegment()
    {
        $staticSegment = new StaticSegment();
        $staticSegment->setSyncStatus(StaticSegment::STATUS_NOT_SYNCED);
        $this->entityRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue($staticSegment));
        $this->managerRegistry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($this->entityManager));
        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($this->entityRepository));

        $entity = new MarketingList();
        $this->assertEquals(false, $this->extension->getEmailCampaignSyncStatus($entity));
    }

    public function testIsNotSegment()
    {
        $staticSegment = null;
        $this->entityRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue($staticSegment));
        $this->managerRegistry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($this->entityManager));
        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($this->entityRepository));

        $entity = new MarketingList();
        $this->assertEquals(false, $this->extension->getEmailCampaignSyncStatus($entity));
    }
}
