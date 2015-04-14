<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\ImportExport\DataConverter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\CartItem;
use OroCRM\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter\MmbrCartMergeVarValuesDataConverter;

class MmbrCartMergeVarValuesDataConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MmbrCartMergeVarValuesDataConverter
     */
    private $converter;

    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $template;

    /**
     * @var EntityRepository
     */
    private $entityRepository;

    protected function setUp()
    {
        $this->doctrineHelper = $this
            ->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->twig = $this->getMockBuilder('\Twig_Environment')->getMock();
        $this->template = 'cartItems.txt.twig';
        $this->converter = new MmbrCartMergeVarValuesDataConverter(
            $this->doctrineHelper,
            $this->twig,
            $this->template
        );
        $this->entityRepository = $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Extended Merge Var cart items template should be provided.
     */
    public function testObjectInitializationWheTemplateIsNotString()
    {
        new MmbrCartMergeVarValuesDataConverter(
            $this->doctrineHelper,
            $this->twig,
            []
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Extended Merge Var cart items template should be provided.
     */
    public function testObjectInitializationWhenTemplaIsEmptyString()
    {
        new MmbrCartMergeVarValuesDataConverter($this->doctrineHelper, $this->twig, '');
    }

    public function testConvertToImportFormatWhenEntityIdIsNotIsset()
    {
        $cartItemsVar = new ExtendedMergeVar();
        $cartItemsVar->setName('cartItems');
        $vars = new ArrayCollection([$cartItemsVar]);

        $importedRecord = [
            'entityClass' => 'Entity',
            'e_fName' => 'John',
            'extended_merge_vars' => $vars
        ];

        $this->doctrineHelper->expects($this->never())->method('getEntityRepository');
        $this->entityRepository->expects($this->never())->method('find');
        $this->twig->expects($this->never())->method('render');

        $result = $this->converter->convertToImportFormat($importedRecord);

        $this->assertTrue(is_array($result));
        $this->assertEmpty($result);
    }

    public function testConvertToImportFormatWhenCartDoesNotExist()
    {
        $cartItemsVar = new ExtendedMergeVar();
        $cartItemsVar->setName('item_1');
        $vars = new ArrayCollection(array($cartItemsVar));

        $importedRecord = [
            'entity_id' => 1,
            'entityClass' => 'Entity',
            'e_fName' => 'John',
            'e_lName' => 'Doe',
            'e_email' => 'john.doe@email.com',
            'extended_merge_vars' => $vars
        ];

        $this->doctrineHelper->expects($this->once())->method('getEntityRepository')
            ->with('Entity')->will($this->returnValue($this->entityRepository));
        $this->entityRepository->expects($this->once())->method('find')->with(1)->will($this->returnValue(null));

        $this->twig->expects($this->never())->method('render');

        $result = $this->converter->convertToImportFormat($importedRecord);

        $this->assertEmpty($result);
    }

    public function testConvertToImportFormatWhenCartHasNoItems()
    {
        $cartItemsVar = new ExtendedMergeVar();
        $cartItemsVar->setName('item_1');
        $vars = new ArrayCollection(array($cartItemsVar));

        $importedRecord = [
            'entity_id' => 1,
            'entityClass' => 'Entity',
            'e_fName' => 'John',
            'e_lName' => 'Doe',
            'e_email' => 'john.doe@email.com',
            'extended_merge_vars' => $vars
        ];

        $cart = new Cart();

        $this->doctrineHelper->expects($this->once())->method('getEntityRepository')
            ->with('Entity')->will($this->returnValue($this->entityRepository));

        $this->entityRepository->expects($this->once())->method('find')->with(1)->will($this->returnValue($cart));

        $this->twig->expects($this->never())->method('render');

        $result = $this->converter->convertToImportFormat($importedRecord);

        $this->assertEmpty($result);
    }

    public function testConvertToImportFormat()
    {
        $firstNameVar = new ExtendedMergeVar();
        $firstCartItemVar = new ExtendedMergeVar();
        $secondCartItemVar = new ExtendedMergeVar();
        $firstNameVar->setName('fName');
        $firstCartItemVar->setName('item_1');
        $secondCartItemVar->setName('item_2');
        $vars = new ArrayCollection(
            [
                $firstNameVar,
                $firstCartItemVar,
                $secondCartItemVar
            ]
        );

        $importedRecord = [
            'entity_id' => 1,
            'entityClass' => 'Entity',
            'e_fName' => 'John',
            'e_lName' => 'Doe',
            'e_email' => 'john.doe@email.com',
            'extended_merge_vars' => $vars
        ];

        $cart = new Cart();
        $cartItem1 = new CartItem();
        $cartItem2 = new CartItem();
        $cartItems = new ArrayCollection([$cartItem1, $cartItem2]);

        $cart->setCartItems($cartItems);

        $this->doctrineHelper->expects($this->once())->method('getEntityRepository')
            ->with('Entity')->will($this->returnValue($this->entityRepository));

        $this->entityRepository->expects($this->once())->method('find')->with(1)->will($this->returnValue($cart));

        $this->twig->expects($this->at(0))->method('render')
            ->with($this->template, ['item' => $cartItem1, 'index' => 0])
            ->will($this->returnValue('rendered_html_of_cart_item_1'));

        $this->twig->expects($this->at(1))->method('render')
            ->with($this->template, ['item' => $cartItem2, 'index' => 1])
            ->will($this->returnValue('rendered_html_of_cart_item_2'));

        $result = $this->converter->convertToImportFormat($importedRecord);

        $this->assertNotEmpty($result);

        $this->assertArrayHasKey($firstCartItemVar->getTag(), $result);
        $this->assertArrayNotHasKey($firstNameVar->getTag(), $result);
        $this->assertEquals('rendered_html_of_cart_item_1', $result[$firstCartItemVar->getTag()]);
        $this->assertEquals('rendered_html_of_cart_item_2', $result[$secondCartItemVar->getTag()]);
    }
}
