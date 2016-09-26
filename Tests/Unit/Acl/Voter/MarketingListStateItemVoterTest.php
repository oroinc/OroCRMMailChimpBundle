<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Acl\Voter;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Query\Expr;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MailChimpBundle\Acl\Voter\MarketingListStateItemVoter;
use Oro\Bundle\MailChimpBundle\Entity\Member;
use Oro\Bundle\MailChimpBundle\Model\FieldHelper;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;

class MarketingListStateItemVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MarketingListStateItemVoter
     */
    protected $voter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContactInformationFieldsProvider
     */
    protected $contactInformationFieldsProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FieldHelper
     */
    protected $fieldHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ObjectManager
     */
    protected $em;

    protected function setUp()
    {
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrineHelper
            ->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($this->em));

        $this->contactInformationFieldsProvider = $this->getMockBuilder(
            'Oro\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->fieldHelper = $this->getMockBuilder('Oro\Bundle\MailChimpBundle\Model\FieldHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->voter = new MarketingListStateItemVoter(
            $this->doctrineHelper,
            $this->contactInformationFieldsProvider,
            $this->fieldHelper,
            '\stdClass',
            '\stdClass',
            '\stdClass'
        );
    }

    /**
     * @param string $attribute
     * @param bool $expected
     * @dataProvider supportsAttributeDataProvider
     */
    public function testSupportsAttribute($attribute, $expected)
    {
        $this->assertEquals($expected, $this->voter->supportsAttribute($attribute));
    }

    /**
     * @return array
     */
    public function supportsAttributeDataProvider()
    {
        return [
            'VIEW' => ['VIEW', false],
            'CREATE' => ['CREATE', false],
            'EDIT' => ['EDIT', false],
            'DELETE' => ['DELETE', true],
            'ASSIGN' => ['ASSIGN', false],
        ];
    }

    /**
     * @param string $class
     * @param string $actualClass
     * @param bool $expected
     * @dataProvider supportsClassDataProvider
     */
    public function testSupportsClass($class, $actualClass, $expected)
    {
        $this->voter->setClassName($actualClass);

        $this->assertEquals($expected, $this->voter->supportsClass($class));
    }

    /**
     * @return array
     */
    public function supportsClassDataProvider()
    {
        return [
            'supported' => ['stdClass', 'stdClass', true],
            'not_supported' => ['NotSupportedClass', 'stdClass', false],
        ];
    }

    /**
     * @param mixed $identifier
     * @param mixed $className
     * @param mixed $object
     * @param bool $expected
     * @param array $attributes
     * @param string|null $queryResult
     * @dataProvider attributesDataProvider
     */
    public function testVote($identifier, $className, $object, $expected, $attributes, $queryResult = null)
    {
        $this->doctrineHelper
            ->expects($this->any())
            ->method('getSingleEntityIdentifier')
            ->will($this->returnValue($identifier));

        $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository
            ->expects($this->any())
            ->method('find')
            ->will(
                $this->returnValueMap(
                    [
                        [$identifier, $this->getItem()],
                        [2, $object]
                    ]
                )
            );

        $this->doctrineHelper
            ->expects($this->any())
            ->method('getEntityRepository')
            ->will($this->returnValue($repository));

        if (is_object($object)) {
            $this->doctrineHelper
                ->expects($this->any())
                ->method('getEntityClass')
                ->will($this->returnValue(get_class($object)));
        }

        $this->contactInformationFieldsProvider
            ->expects($this->any())
            ->method('getEntityTypedFields')
            ->will($this->returnValue(['email']));

        $this->contactInformationFieldsProvider
            ->expects($this->any())
            ->method('getTypedFieldsValues')
            ->will($this->returnValue(['email']));

        $this->em
            ->expects($this->any())
            ->method('createQueryBuilder')
            ->will($this->returnValue($this->getQueryBuilderMock($queryResult)));

        $this->voter->setClassName($className);

        /** @var TokenInterface $token */
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $this->assertEquals(
            $expected,
            $this->voter->vote($token, $object, $attributes)
        );
    }

    /**
     * @return array
     */
    public function attributesDataProvider()
    {
        return [
            [null, null, [], MarketingListStateItemVoter::ACCESS_ABSTAIN, []],
            [null, null, new \stdClass(), MarketingListStateItemVoter::ACCESS_ABSTAIN, []],
            [1, null, new \stdClass(), MarketingListStateItemVoter::ACCESS_ABSTAIN, ['VIEW']],
            [1, 'NotSupports', new \stdClass(), MarketingListStateItemVoter::ACCESS_ABSTAIN, ['DELETE']],
            [1, 'stdClass', new \stdClass(), MarketingListStateItemVoter::ACCESS_ABSTAIN, ['DELETE']],
            [1, 'stdClass', new \stdClass(), MarketingListStateItemVoter::ACCESS_ABSTAIN, ['DELETE'], '0'],
            [1, 'stdClass', new \stdClass(), MarketingListStateItemVoter::ACCESS_DENIED, ['DELETE'], '1'],
            [1, 'stdClass', new \stdClass(), MarketingListStateItemVoter::ACCESS_DENIED, ['DELETE'], '2'],
            [1, 'stdClass', null, MarketingListStateItemVoter::ACCESS_ABSTAIN, ['DELETE'], '2'],
        ];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getItem()
    {
        $item = $this->getMock('Oro\Bundle\MarketingListBundle\Entity\MarketingListStateItemInterface');
        $marketingList = $this->getMock('Oro\Bundle\MarketingListBundle\Entity\MarketingList');

        $item
            ->expects($this->any())
            ->method('getMarketingList')
            ->will($this->returnValue($marketingList));

        $item
            ->expects($this->any())
            ->method('getEntityId')
            ->will($this->returnValue(2));

        $marketingList
            ->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue('stdClass'));

        return $item;
    }

    /**
     * @param mixed $queryResult
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getQueryBuilderMock($queryResult)
    {
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $qb
            ->expects($this->any())
            ->method('expr')
            ->will($this->returnValue(new Expr()));

        $qb
            ->expects($this->any())
            ->method('select')
            ->will($this->returnSelf());

        $qb
            ->expects($this->any())
            ->method('from')
            ->will($this->returnSelf());

        $qb
            ->expects($this->any())
            ->method('join')
            ->will($this->returnSelf());

        $qb
            ->expects($this->any())
            ->method('where')
            ->will($this->returnSelf());

        $qb
            ->expects($this->any())
            ->method('setMaxResults')
            ->with(1)
            ->will($this->returnSelf());

        $qb
            ->expects($this->any())
            ->method('setParameter')
            ->with($this->isType('string'), [Member::STATUS_UNSUBSCRIBED, Member::STATUS_CLEANED])
            ->will($this->returnSelf());

        $query = $this
            ->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(['getScalarResult'])
            ->getMockForAbstractClass();

        $qb
            ->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $query
            ->expects($this->any())
            ->method('getScalarResult')
            ->will($this->returnValue($queryResult));

        return $qb;
    }
}
