<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Autocomplete;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use OroCRM\Bundle\MailChimpBundle\Autocomplete\TemplateSearchHandler;
use Oro\Bundle\SearchBundle\Query\Result;
use OroCRM\Bundle\MailChimpBundle\Entity\Template;

class TemplateSearchHandlerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ENTITY_CLASS = 'FooEntityClass';
    const ID = 'id';

    /**
     * @var array
     */
    protected $testProperties = array('name', 'email');

    /**
     * @var TemplateSearchHandler
     */
    protected $searchHandler;

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
     * @var QueryBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $queryBuilder;

    /**
     * @var AbstractQuery|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $query;

    /**
     * @var AclHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aclHelper;

    protected function setUp()
    {
        $this->queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('expr', 'getQuery', 'where', 'andWhere', 'addOrderBy', 'setParameter', 'getResult'))
            ->getMock();
        $this->entityRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('createQueryBuilder', 'findOneBy'))
            ->getMock();
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getRepository', 'getMetadataFactory'))
            ->getMock();
        $this->managerRegistry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->expr = $this->getMockBuilder('Doctrine\ORM\Query\Expr')
            ->disableOriginalConstructor()
            ->setMethods(array('in', 'like'))
            ->getMock();
        $this->aclHelper = $this->getMockBuilder('Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper')
            ->disableOriginalConstructor()
            ->setMethods(array('apply'))
            ->getMock();

        $this->searchHandler = new TemplateSearchHandler(self::TEST_ENTITY_CLASS, $this->testProperties);
        $this->searchHandler->setAclHelper($this->aclHelper);
    }

    protected function setUpExpects()
    {
        $metadataFactory = $this->setMetaMocks();

        $this->entityRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnArgument(0));
        $this->entityRepository
            ->expects($this->any())
            ->method('createQueryBuilder')
            ->will($this->returnValue($this->queryBuilder));

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($this->entityRepository));
        $this->entityManager->expects($this->once())
            ->method('getMetadataFactory')
            ->will($this->returnValue($metadataFactory));
        $this->managerRegistry->expects($this->once())
            ->method('getManagerForClass')
            ->with(self::TEST_ENTITY_CLASS)
            ->will($this->returnValue($this->entityManager));

        $this->searchHandler->initDoctrinePropertiesByManagerRegistry($this->managerRegistry);
    }

    public function testSearchEntitiesQueryBuilder()
    {
        $this->setUpExpects();
        $this->setSearchExpects();

        $reflection = new \ReflectionClass(get_class($this->searchHandler));
        $method = $reflection->getMethod('searchEntities');
        $method->setAccessible(true);

        $search = "test;1";
        $firstResult = 1;
        $maxResults = 10;
        $result = $method->invokeArgs($this->searchHandler, array($search, $firstResult, $maxResults));
        $this->assertEquals($result, '');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Search handler is not fully configured
     */
    public function testCheckDependenciesInjectedFail()
    {
        $this->searchHandler->search("", 1, 1);
    }

    public function testFindByIdArrayArgumentsInRequest()
    {
        $this->setUpExpects();

        $reflection = new \ReflectionClass(get_class($this->searchHandler));
        $method = $reflection->getMethod('findById');
        $method->setAccessible(true);
        $result = $method->invokeArgs($this->searchHandler, array('1;1'));

        $this->assertEquals($result, array('id' => 1, 'channel' => 1));
    }

    /**
     * @dataProvider templateConvertDataProvider
     */
    public function testConvertItemsWithCategory($expect)
    {
        $this->setUpExpects();

        $reflection = new \ReflectionClass(get_class($this->searchHandler));
        $method = $reflection->getMethod('convertItems');
        $method->setAccessible(true);

        $templateOne = new Template();
        $templateOne->setCategory($expect[0]['name'])->setName($expect[0]['children'][0]['name']);
        $templateTwo = new Template();
        $templateTwo->setCategory($expect[1]['name'])->setName($expect[1]['children'][0]['name']);
        $templates = array($templateOne, $templateTwo);
        $result = $method->invokeArgs($this->searchHandler, array($templates));
        $this->assertEquals($result, $expect);
    }

    /**
     * @dataProvider templateConvertDataProvider
     */
    public function testConvertItemsWithType($expect)
    {
        $this->setUpExpects();

        $reflection = new \ReflectionClass(get_class($this->searchHandler));
        $method = $reflection->getMethod('convertItems');
        $method->setAccessible(true);

        $templateOne = new Template();
        $templateOne->setType($expect[0]['name'])->setName($expect[0]['children'][0]['name']);
        $templateTwo = new Template();
        $templateTwo->setType($expect[1]['name'])->setName($expect[1]['children'][0]['name']);
        $templates = array($templateOne, $templateTwo);
        $result = $method->invokeArgs($this->searchHandler, array($templates));
        $this->assertEquals($result, $expect);
    }

    /**
     * @dataProvider templateConvertDataProvider
     */
    public function testSearchEntitiesValidResult($expect)
    {
        $this->setUpExpects();
        $this->setSearchExpects();

        $templateOne = new Template();
        $templateOne->setCategory($expect[0]['name'])->setName($expect[0]['children'][0]['name']);
        $templateTwo = new Template();
        $templateTwo->setType($expect[1]['name'])->setName($expect[1]['children'][0]['name']);
        $templateTree = new Template();
        $templateTree->setType($expect[1]['name'] . '3')->setName($expect[1]['children'][0]['name']);
        $templateFour = new Template();
        $templateFour->setType($expect[1]['name'] . '4')->setName($expect[1]['children'][0]['name']);
        $templates = array($templateOne, $templateTwo, $templateTree, $templateFour);
        $this->queryBuilder->expects($this->exactly(1))
            ->method('getResult')
            ->will($this->returnValue($templates));

        $search = "test;1";
        $firstResult = 1;
        $maxResults = 2;
        $result = $this->searchHandler->search($search, $firstResult, $maxResults);
        $this->assertEquals($result['more'], false);
        $this->assertEquals(count($result['results']), 4);
    }

    /**
     * @dataProvider templateConvertDataProvider
     */
    public function testSearchEntitiesByIdValidResult($expect)
    {
        $this->setUpExpects();
        $templateOne = new Template();
        $templateOne->setCategory($expect[0]['name'])->setName($expect[0]['children'][0]['name']);
        $this->entityRepository->expects($this->exactly(1))
            ->method('findOneBy')
            ->will($this->returnValue($templateOne));
        $search = "test;1";
        $firstResult = 1;
        $maxResults = 2;
        $result = $this->searchHandler->search($search, $firstResult, $maxResults, true);
        $this->assertEquals($result['more'], false);
        $this->assertEquals(count($result['results']), 1);
        $this->assertEquals($result['results'][0]['id'], "test");
    }

    /**
     * @return array
     */
    public function templateConvertDataProvider()
    {
        return
            array(
                array(
                    array(
                        array(
                            "name" => 'C1',
                            "children" => array(
                                array(
                                    "id" => null,
                                    "name" => "Name",
                                    "email" => null,
                                )
                            )
                        ),
                        array(
                            "name" => 'C2',
                            "children" => array(
                                array(
                                    "id" => null,
                                    "name" => null,
                                    "email" => null,
                                )
                            )
                        )
                    )
                )
            );
    }

    /**
     * @return mixed
     */
    protected function setMetaMocks()
    {
        $metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->setMethods(array('getSingleIdentifierFieldName'))
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects($this->once())
            ->method('getSingleIdentifierFieldName')
            ->will($this->returnValue(self::ID));
        $metadataFactory = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataFactory')
            ->setMethods(array('getMetadataFor'))
            ->disableOriginalConstructor()
            ->getMock();
        $metadataFactory->expects($this->once())
            ->method('getMetadataFor')
            ->with(self::TEST_ENTITY_CLASS)
            ->will($this->returnValue($metadata));

        return $metadataFactory;
    }

    protected function setSearchExpects()
    {
        $this->entityRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($this->queryBuilder));
        $this->queryBuilder->expects($this->once())
            ->method('where')
            ->will($this->returnSelf());
        $this->queryBuilder->expects($this->exactly(2))
            ->method('andWhere')
            ->will($this->returnSelf());
        $this->queryBuilder->expects($this->exactly(3))
            ->method('setParameter')
            ->will($this->returnSelf());
        $this->queryBuilder->expects($this->exactly(2))
            ->method('addOrderBy')
            ->will($this->returnSelf());

        $this->queryBuilder
            ->expects($this->any())
            ->method('expr')
            ->will($this->returnValue($this->expr));
        $this->aclHelper->expects($this->once())
            ->method('apply')
            ->will($this->returnValue($this->queryBuilder));
    }
}
