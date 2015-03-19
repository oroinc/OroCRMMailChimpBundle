<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Model\ExtendedMergeVar;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use OroCRM\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Model\ExtendedMergeVar\QueryDecorator;
use OroCRM\Bundle\MailChimpBundle\Model\FieldHelper;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class QueryDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QueryDecorator
     */
    private $queryDecorator;

    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var StaticSegment
     */
    private $staticSegment;

    /**
     * @var MarketingList
     */
    private $marketingList;

    protected function setUp()
    {
        $this->fieldHelper = $this
            ->getMockBuilder('OroCRM\Bundle\MailChimpBundle\Model\FieldHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryDecorator = new QueryDecorator($this->fieldHelper);

        $this->queryBuilder = $this
            ->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->staticSegment = $this
            ->getMockBuilder('OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment')
            ->disableOriginalConstructor()
            ->getMock();
        $this->marketingList = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testDecorate()
    {
        $extendedMergeVar = new ExtendedMergeVar();
        $cartItemsMergeVar = new ExtendedMergeVar();
        $extendedMergeVar->setName('extendedMergeVar');
        $cartItemsMergeVar->setName('item_1');
        $extendedMergeVars = new ArrayCollection(
            array(
                $extendedMergeVar, $cartItemsMergeVar
            )
        );
        $this->staticSegment->expects($this->exactly(2))->method('getExtendedMergeVars')
            ->will($this->returnValue($extendedMergeVars));
        $this->staticSegment->expects($this->once())->method('getMarketingList')
            ->will($this->returnValue($this->marketingList));

        $this->marketingList->expects($this->once())->method('getEntity')->will($this->returnValue('EntityClass'));

        $this->fieldHelper->expects($this->once())->method('getFieldExpr')
            ->with('EntityClass', $this->queryBuilder, $extendedMergeVar->getName())
            ->will($this->returnValue('field_expr'));

        $this->queryBuilder->expects($this->once())->method('addSelect')
            ->with('field_expr AS ' . $extendedMergeVar->getNameWithPrefix());

        $this->queryDecorator->decorate($this->queryBuilder, $this->staticSegment);
    }
}
