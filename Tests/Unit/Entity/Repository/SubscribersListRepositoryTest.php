<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Entity\Repository;

use Doctrine\ORM\Mapping\ClassMetadata;
use OroCRM\Bundle\MailChimpBundle\Entity\Repository\SubscribersListRepository;

class SubscribersListRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SubscribersListRepository
     */
    protected $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    protected function setUp()
    {
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->repository = new SubscribersListRepository(
            $this->entityManager,
            new ClassMetadata('OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList')
        );
    }

    protected function tearDown()
    {
        unset($this->entityManager);
        unset($this->repository);
    }

    /**
     *
     */
    public function testGetAllSubscribersListIterator()
    {
//        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
//            ->disableOriginalConstructor()
//            ->getMock();
//        $qb->expects($this->exactly(2))
//            ->method('select')
//            ->will($this->returnSelf());
//        $qb->expects($this->once())
//            ->method('from')
//            ->will($this->returnSelf());
//        $qb->expects($this->once())
//            ->method('where')
//            ->will($this->returnSelf());
//        $qb->expects($this->once())
//            ->method('orWhere')
//            ->will($this->returnSelf());
//        $qb->expects($this->once())
//            ->method('andWhere')
//            ->will($this->returnSelf());
//        $qb->expects($this->once())
//            ->method('orderBy')
//            ->will($this->returnSelf());
//        $qb->expects($this->exactly(2))
//            ->method('setParameter')
//            ->will($this->returnSelf());

//        $this->entityManager->expects($this->once())
//            ->method('createQueryBuilder')
//            ->will($this->returnValue($qb));
//
//        $this->repository->getAllSubscribersListIterator();
    }
}
