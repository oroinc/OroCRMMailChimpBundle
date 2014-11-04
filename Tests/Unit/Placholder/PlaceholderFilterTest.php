<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Placeholder;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ManagerRegistry;

use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Placeholder\PlaceholderFilter;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class PlaceholderFilterTest extends \PHPUnit_Framework_TestCase
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
            ->setMethods(array('getRepository', 'getMetadataFactory'))
            ->getMock();
        $this->managerRegistry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');

        $this->placeholderFilter = new PlaceholderFilter($this->managerRegistry);
    }

    public function testIsNotApplicableEntityOnMarketingList()
    {
        $entity = $this->getMock('OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign');
        $this->placeholderFilter->isApplicableOnMarketingList($entity);

        $this->assertFalse($this->placeholderFilter->isApplicableOnMarketingList($entity));
    }

    /**
     * @param $staticSegment
     * @param $expected
     * @dataProvider staticSegmentDataProvider
     */
    public function testIsApplicableOnMarketingList($staticSegment, $expected)
    {
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
        $this->assertEquals($expected, $this->placeholderFilter->isApplicableOnMarketingList($entity));
    }

    /**
     * @return array
     */
    public function staticSegmentDataProvider()
    {
        return [
            [null, false],
            [new StaticSegment(), true],
        ];
    }
}
