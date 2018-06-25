<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Model;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\EntityBundle\Provider\VirtualFieldProviderInterface;
use Oro\Bundle\MailChimpBundle\Model\FieldHelper;

class FieldHelperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|VirtualFieldProviderInterface
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

        /** @var \PHPUnit\Framework\MockObject\MockObject|QueryBuilder $qb */
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

    /**
     * @param string $entityClass
     * @param string $fieldName
     * @param string $alias
     * @param array $fieldConfig
     * @param array $joins
     * @param string $expected
     *
     * @dataProvider virtualFieldsProvider
     */
    public function testGetFieldExprVirtual(
        $entityClass,
        $fieldName,
        $alias,
        array $fieldConfig,
        array $joins,
        $expected
    ) {
        $from = $this->getMockBuilder('Doctrine\ORM\Query\Expr\From')
            ->disableOriginalConstructor()
            ->getMock();
        $from->expects($this->atLeastOnce())
            ->method('getAlias')
            ->will($this->returnValue($alias));

        /** @var \PHPUnit\Framework\MockObject\MockObject|QueryBuilder $qb */
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $qb->expects($this->atLeastOnce())
            ->method('getDQLPart')
            ->will(
                $this->returnValueMap(
                    [
                        ['from', [$from]],
                        ['join', $joins]
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

        $this->assertEquals($expected, $this->helper->getFieldExpr($entityClass, $qb, $fieldName));
    }

    /**
     * @return array
     */
    public function virtualFieldsProvider()
    {
        return [
            'has_join' => [
                'stdClass',
                'field',
                't1',
                [
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
                ],
                [
                    't1' => [
                        new Join('LEFT', 't1.emails', 't2', 'WITH', 't2.primary = true'),
                        new Join('LEFT', 't1.phones', 't3', 'WITH', 't3.primary = true'),
                        new Join('INNER', 't1.account', 't4', 'WITH', 't4.id = t1.account_id'),
                    ]
                ],
                't2.email'
            ],
            'empty_qb' => [
                'stdClass',
                'field',
                't1',
                [
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
                ],
                [],
                'emails.email'
            ]
        ];
    }
}
