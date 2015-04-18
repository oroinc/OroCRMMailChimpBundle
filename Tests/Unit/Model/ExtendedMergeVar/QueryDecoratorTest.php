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

    public function testDecorate()
    {
        $selects = [
            new Select('t1.email as c1'),
            new Select('t1.fname as c2'),
            new Select('t1.id'),
            new Select('t1.lname as c3'),
            new Select('name')
        ];
        $this->queryBuilder->expects($this->once())->method('getDQLPart')
            ->with('select')->will($this->returnValue($selects));
        $this->queryBuilder->expects($this->once())->method('resetDQLPart')->with('select');
        $this->queryBuilder->expects($this->once())->method('getRootAliases')
            ->will($this->returnValue(array('t1')));

        $this->queryBuilder->expects($this->at(3))->method('addSelect')->with($selects[0]);
        $this->queryBuilder->expects($this->at(4))->method('addSelect')->with('t1.email as c1_email');
        $this->queryBuilder->expects($this->at(5))->method('addSelect')->with($selects[1]);
        $this->queryBuilder->expects($this->at(6))->method('addSelect')->with('t1.fname as c2_fname');
        $this->queryBuilder->expects($this->at(7))->method('addSelect')->with($selects[3]);
        $this->queryBuilder->expects($this->at(8))->method('addSelect')->with('t1.lname as c3_lname');
        $this->queryBuilder->expects($this->at(9))->method('addSelect')->with($selects[4]);

        $this->queryDecorator->decorate($this->queryBuilder);
    }
}
