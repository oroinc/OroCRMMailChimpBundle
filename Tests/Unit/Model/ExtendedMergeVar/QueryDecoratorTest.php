<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Model\ExtendedMergeVar;

use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\QueryBuilder;

use OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar\QueryDecorator;

class QueryDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QueryDecorator
     */
    protected $queryDecorator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|QueryBuilder
     */
    protected $queryBuilder;

    protected function setUp()
    {
        $this->queryDecorator = new QueryDecorator();

        $this->queryBuilder = $this
            ->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown()
    {
        unset($this->queryBuilder);
        unset($this->queryDecorator);
    }

    /**
     * @dataProvider selectsDataProvider
     * @param string|array $select
     * @param array $expected
     */
    public function testDecorate($select, array $expected = [])
    {
        $selects = [
            new Select($select)
        ];

        $this->queryBuilder->expects($this->once())->method('getDQLPart')
            ->with('select')->will($this->returnValue($selects));

        foreach ($expected as $index => $expectedSelect) {
            $this->queryBuilder->expects($this->at($index))->method('addSelect')->with($expectedSelect);
        }

        if (empty($expected)) {
            $this->queryBuilder->expects($this->never())->method('addSelect');
        }

        $this->queryDecorator->decorate($this->queryBuilder);
    }

    /**
     * @return array
     */
    public function selectsDataProvider()
    {
        return [
            [
                't1.email as c1',
                [1 => 't1.email as c1_email']
            ],
            [
                't1.fname as c2',
                [1 => 't1.fname as c2_fname']
            ],
            [
                't1.id',
                []
            ],
            [
                ['t1.lname AS c3', 'name'],
                [1 => 't1.lname as c3_lname']
            ],
            [
                '1 as entity',
                []
            ],
            [
                't1.status as c4, t1.total as c5',
                [1 => 't1.status as c4_status', 2 => 't1.total as c5_total']
            ]
        ];
    }
}
