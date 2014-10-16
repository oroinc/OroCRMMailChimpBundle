<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Entity\Repository;

use Doctrine\ORM\Mapping\ClassMetadata;
use OroCRM\Bundle\MailChimpBundle\Entity\Repository\SubscribersListRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\EntityManager;

class SubscribersListRepositoryTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ENTITY_CLASS = 'FooEntityClass';
    const ID = 'id';

    /**
     * @var SubscribersListRepository
     */
    protected $repository;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var QueryBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $queryBuilder;

    protected function setUp()
    {
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('createQueryBuilder'))
            ->getMock();

        $this->queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('select', 'from', 'where', 'orderBy', 'setParameter', 'getQuery'))
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

    public function testGetAllSubscribersListIterator()
    {
        $this->queryBuilder->expects($this->exactly(2))
            ->method('select')
            ->will($this->returnSelf());
        $this->queryBuilder->expects($this->once())
            ->method('from')
            ->will($this->returnSelf());

        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($this->queryBuilder));

        $this->repository->getAllSubscribersListIterator();
    }
}
