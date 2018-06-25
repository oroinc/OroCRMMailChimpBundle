<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Autocomplete;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\MailChimpBundle\Autocomplete\TemplateSearchHandler;
use Oro\Bundle\MailChimpBundle\Entity\Template;
use Oro\Bundle\SearchBundle\Query\Result;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class TemplateSearchHandlerTest extends \PHPUnit\Framework\TestCase
{
    const TEST_ENTITY_CLASS = 'FooEntityClass';
    const ID = 'id';

    /**
     * @var array
     */
    protected $testProperties = ['name', 'email'];

    /**
     * @var TemplateSearchHandler
     */
    protected $searchHandler;

    /**
     * @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $managerRegistry;

    /**
     * @var EntityManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityManager;

    /**
     * @var EntityRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityRepository;

    /**
     * @var QueryBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $queryBuilder;

    /**
     * @var AbstractQuery|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $query;

    /**
     * @var AclHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $aclHelper;

    /**
     * @var Expr|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $expr;

    protected function setUp()
    {
        $this->queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['expr', 'getQuery', 'where', 'andWhere', 'addOrderBy', 'setParameter', 'getResult'])
            ->getMock();
        $this->entityRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(['createQueryBuilder', 'findOneBy'])
            ->getMock();
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(['getRepository', 'getMetadataFactory'])
            ->getMock();
        $this->managerRegistry = $this->createMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->expr = $this->getMockBuilder('Doctrine\ORM\Query\Expr')
            ->disableOriginalConstructor()
            ->setMethods(['in', 'like'])
            ->getMock();
        $this->aclHelper = $this->getMockBuilder('Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper')
            ->disableOriginalConstructor()
            ->setMethods(['apply'])
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
        $result = $method->invokeArgs($this->searchHandler, [$search, $firstResult, $maxResults]);
        $this->assertEmpty($result);
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
        $result = $method->invokeArgs($this->searchHandler, ['1;1']);

        $this->assertEquals([['id' => 1, 'channel' => 1]], $result);
    }

    /**
     * @param array $expected
     *
     * @dataProvider templateConvertDataProvider
     */
    public function testConvertItemsWithCategory(array $expected)
    {
        $this->setUpExpects();

        $reflection = new \ReflectionClass(get_class($this->searchHandler));
        $method = $reflection->getMethod('convertItems');
        $method->setAccessible(true);

        $templateOne = new Template();
        $templateOne->setCategory($expected[0]['name'])->setName($expected[0]['children'][0]['name']);
        $templateTwo = new Template();
        $templateTwo->setCategory($expected[1]['name'])->setName($expected[1]['children'][0]['name']);
        $templates = [$templateOne, $templateTwo];
        $result = $method->invokeArgs($this->searchHandler, [$templates]);
        $this->assertEquals($expected, $result);
    }

    /**
     * @param array $expected
     *
     * @dataProvider templateConvertDataProvider
     */
    public function testConvertItemsWithType(array $expected)
    {
        $this->setUpExpects();

        $reflection = new \ReflectionClass(get_class($this->searchHandler));
        $method = $reflection->getMethod('convertItems');
        $method->setAccessible(true);

        $templateOne = new Template();
        $templateOne->setType($expected[0]['name'])->setName($expected[0]['children'][0]['name']);
        $templateTwo = new Template();
        $templateTwo->setType($expected[1]['name'])->setName($expected[1]['children'][0]['name']);
        $templates = [$templateOne, $templateTwo];
        $result = $method->invokeArgs($this->searchHandler, [$templates]);
        $this->assertEquals($expected, $result);
    }

    /**
     * @param array $expected
     *
     * @dataProvider templateConvertDataProvider
     */
    public function testSearchEntitiesValidResult(array $expected)
    {
        $this->setUpExpects();
        $this->setSearchExpects();

        $templateOne = new Template();
        $templateOne->setCategory($expected[0]['name'])->setName($expected[0]['children'][0]['name']);
        $templateTwo = new Template();
        $templateTwo->setType($expected[1]['name'])->setName($expected[1]['children'][0]['name']);
        $templateTree = new Template();
        $templateTree->setType($expected[1]['name'] . '3')->setName($expected[1]['children'][0]['name']);
        $templateFour = new Template();
        $templateFour->setType($expected[1]['name'] . '4')->setName($expected[1]['children'][0]['name']);
        $templates = [$templateOne, $templateTwo, $templateTree, $templateFour];
        $this->queryBuilder->expects($this->exactly(1))
            ->method('getResult')
            ->will($this->returnValue($templates));

        $search = "test;1";
        $firstResult = 1;
        $maxResults = 2;
        $result = $this->searchHandler->search($search, $firstResult, $maxResults);
        $this->assertFalse($result['more']);
        $this->assertCount(4, $result['results']);
    }

    /**
     * @param array $expected
     *
     * @dataProvider templateConvertDataProvider
     */
    public function testSearchEntitiesByIdValidResult(array $expected)
    {
        $this->setUpExpects();
        $templateOne = new Template();
        $templateOne->setCategory($expected[0]['name'])->setName($expected[0]['children'][0]['name']);
        $this->entityRepository->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue($templateOne));
        $search = "test;1";
        $firstResult = 1;
        $maxResults = 2;
        $result = $this->searchHandler->search($search, $firstResult, $maxResults, true);
        $this->assertFalse($result['more']);
        $this->assertCount(1, $result['results']);
        $this->assertEquals("test", $result['results'][0]['id']);
    }

    /**
     * @return array
     */
    public function templateConvertDataProvider()
    {
        return
            [
                [
                    [
                        [
                            "name" => 'C1',
                            "children" => [
                                [
                                    "id" => null,
                                    "name" => "Name",
                                    "email" => null,
                                ]
                            ]
                        ],
                        [
                            "name" => 'C2',
                            "children" => [
                                [
                                    "id" => null,
                                    "name" => null,
                                    "email" => null,
                                ]
                            ]
                        ]
                    ]
                ]
            ];
    }

    /**
     * @return mixed
     */
    protected function setMetaMocks()
    {
        $metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->setMethods(['getSingleIdentifierFieldName'])
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->expects($this->once())
            ->method('getSingleIdentifierFieldName')
            ->will($this->returnValue(self::ID));
        $metadataFactory = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataFactory')
            ->setMethods(['getMetadataFor'])
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
