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
        $this->template = 'cartItems.html.twig';
        $this->converter = new MmbrCartMergeVarValuesDataConverter(
            $this->doctrineHelper, $this->twig, $this->template
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
            $this->doctrineHelper, $this->twig, array()
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Extended Merge Var cart items template should be provided.
     */
    public function testObjectInitializationWhenTemplaIsEmptyString()
    {
        new MmbrCartMergeVarValuesDataConverter(
            $this->doctrineHelper, $this->twig, ''
        );
    }

    public function testConvertToImportFormatWhenEntityIdIsNotIsset()
    {
        $cartItemsVar = new ExtendedMergeVar();
        $cartItemsVar->setName('cartItems');
        $vars = new ArrayCollection(
            array(
                $cartItemsVar
            )
        );

        $importedRecord = array(
            'entityClass' => 'Entity',
            'e_fName' => 'John',
            'extended_merge_vars' => $vars
        );

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
        $cartItemsVar->setName('cartItems');
        $vars = new ArrayCollection(array($cartItemsVar));

        $importedRecord = array(
            'entity_id' => 1,
            'entityClass' => 'Entity',
            'e_fName' => 'John',
            'e_lName' => 'Doe',
            'e_email' => 'john.doe@email.com',
            'extended_merge_vars' => $vars
        );

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
        $cartItemsVar->setName('cartItems');
        $vars = new ArrayCollection(array($cartItemsVar));

        $importedRecord = array(
            'entity_id' => 1,
            'entityClass' => 'Entity',
            'e_fName' => 'John',
            'e_lName' => 'Doe',
            'e_email' => 'john.doe@email.com',
            'extended_merge_vars' => $vars
        );

        $cart = new Cart();

        $this->doctrineHelper->expects($this->once())->method('getEntityRepository')
            ->with('Entity')->will($this->returnValue($this->entityRepository));

        $this->entityRepository->expects($this->once())->method('find')->with(1)->will($this->returnValue($cart));

        $this->twig->expects($this->never())->method('render');

        $result = $this->converter->convertToImportFormat($importedRecord);

        $this->assertNotEmpty($result);

        $this->assertArrayHasKey($cartItemsVar->getTag(), $result);
        $this->assertEquals('', $result[$cartItemsVar->getTag()]);
    }

    public function testConvertToImportFormat()
    {
        $firstNameVar = new ExtendedMergeVar();
        $cartItemsVar = new ExtendedMergeVar();
        $firstNameVar->setName('fName');
        $cartItemsVar->setName('cartItems');
        $vars = new ArrayCollection(
            array(
                $firstNameVar,
                $cartItemsVar
            )
        );

        $importedRecord = array(
            'entity_id' => 1,
            'entityClass' => 'Entity',
            'e_fName' => 'John',
            'e_lName' => 'Doe',
            'e_email' => 'john.doe@email.com',
            'extended_merge_vars' => $vars
        );

        $cart = new Cart();
        $cartItems = new ArrayCollection(
            array(
                new CartItem(),
                new CartItem()
            )
        );
        $cart->setCartItems($cartItems);

        $this->doctrineHelper->expects($this->once())->method('getEntityRepository')
            ->with('Entity')->will($this->returnValue($this->entityRepository));

        $this->entityRepository->expects($this->once())->method('find')->with(1)->will($this->returnValue($cart));

        $this->twig->expects($this->once())->method('render')
            ->with($this->template, array('cartItems' => $cartItems))
            ->will($this->returnValue('rendered_html'));

        $result = $this->converter->convertToImportFormat($importedRecord);

        $this->assertNotEmpty($result);

        $this->assertArrayHasKey($cartItemsVar->getTag(), $result);
        $this->assertArrayNotHasKey($firstNameVar->getTag(), $result);
        $this->assertEquals('rendered_html', $result[$cartItemsVar->getTag()]);
    }
}
