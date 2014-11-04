<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Model;

use Doctrine\ORM\Query\Expr\Join;
use OroCRM\Bundle\MailChimpBundle\Model\FieldHelper;

class FieldHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $virtualFieldProvider;

    /**
     * @var FieldHelper
     */
    protected $helper;

    protected function setUp()
    {
        $this->virtualFieldProvider = $this
            ->getMockBuilder('Oro\Bundle\EntityBundle\Provider\VirtualFieldProviderInterface')
            ->getMock();

        $this->helper = new FieldHelper($this->virtualFieldProvider);
    }

    public function testGetFieldExprNotVirtual()
    {
        $entityClass = 'stdClass';
        $fieldName = 'some';
        $alias = 'alias1';

        $from = $this->getMockBuilder('Doctrine\ORM\Query\Expr\From')
            ->disableOriginalConstructor()
            ->getMock();
        $from->expects($this->once())
            ->method('getAlias')
            ->will($this->returnValue($alias));
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $qb->expects($this->once())
            ->method('getDQLPart')
            ->with('from')
            ->will($this->returnValue([$from]));

        $this->virtualFieldProvider->expects($this->once())
            ->method('isVirtualField')
            ->with($entityClass, $fieldName)
            ->will($this->returnValue(false));

        $this->assertEquals('alias1.some', $this->helper->getFieldExpr($entityClass, $qb, $fieldName));
    }

    public function testGetFieldExprVirtual()
    {
        $entityClass = 'stdClass';
        $fieldName = 'some';
        $alias = 't1';
        $fieldConfig = [
            'join' => [
                'left' => [
                    [
                        'join' => 'entity.emails',
                        'alias' => 'emails',
                        'conditionType' => 'WITH',
                        'condition' => 'emails.primary = true'
                    ]
                ]
            ],
            'select' => [
                'expr' => 'emails.email'
            ]
        ];

        $joinOne = new Join('LEFT', 't1.emails', 't2', 'WITH', 't2.primary = true');
        $joinTwo = new Join('LEFT', 't1.phones', 't3', 'WITH', 't3.primary = true');
        $joinThree = new Join('INNER', 't1.account', 't4', 'WITH', 't4.id = t1.account_id');
        $qbJoins = [$alias => [$joinOne, $joinTwo, $joinThree]];

        $from = $this->getMockBuilder('Doctrine\ORM\Query\Expr\From')
            ->disableOriginalConstructor()
            ->getMock();
        $from->expects($this->atLeastOnce())
            ->method('getAlias')
            ->will($this->returnValue($alias));
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $qb->expects($this->atLeastOnce())
            ->method('getDQLPart')
            ->will(
                $this->returnValueMap(
                    [
                        ['from', [$from]],
                        ['join', $qbJoins]
                    ]
                )
            );

        $this->virtualFieldProvider->expects($this->once())
            ->method('isVirtualField')
            ->with($entityClass, $fieldName)
            ->will($this->returnValue(true));
        $this->virtualFieldProvider->expects($this->once())
            ->method('getVirtualFieldQuery')
            ->with($entityClass, $fieldName)
            ->will($this->returnValue($fieldConfig));

        $this->assertEquals('t2.email', $this->helper->getFieldExpr($entityClass, $qb, $fieldName));
    }
}
