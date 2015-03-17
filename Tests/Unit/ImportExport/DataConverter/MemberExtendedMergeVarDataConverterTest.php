<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\ImportExport\DataConverter;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\ImportExportBundle\Converter\DataConverterInterface;
use OroCRM\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter\MemberExtendedMergeVarDataConverter;

class MemberExtendedMergeVarDataConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MemberExtendedMergeVarDataConverter
     */
    private $converter;

    /**
     * @var DataConverterInterface
     */
    private $injectedDataConverter;

    protected function setUp()
    {
        $this->injectedDataConverter = $this
            ->getMockBuilder('Oro\Bundle\ImportExportBundle\Converter\DataConverterInterface')
            ->getMock();
        $this->injectedDataConverter
            ->expects($this->any())
            ->method('convertToImportFormat')
            ->will($this->returnValue(array()));
        $this->converter = new MemberExtendedMergeVarDataConverter($this->injectedDataConverter);
    }

    public function testConvertToImportFormatWhenImportedRecordIsNotValid()
    {
        $result = $this->converter->convertToImportFormat(array());

        $this->assertTrue(is_array($result));
        $this->assertEmpty($result);

        $result = $this->converter
            ->convertToImportFormat(
                array(
                    'extended_merge_vars' => new ArrayCollection(array())
                )
            );

        $this->assertTrue(is_array($result));
        $this->assertEmpty($result);

        $result = $this->converter->convertToImportFormat(array('name' => array()));

        $this->assertTrue(is_array($result));
        $this->assertEmpty($result);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Each element in extended_merge_vars array should be ExtendedMergeVar object.
     */
    public function testConvertToImportFormatWhenExtendedMergeVarsInvalid()
    {
        $this->converter->convertToImportFormat(
            array(
                'extended_merge_vars' => new ArrayCollection(array(new \StdClass()))
            )
        );
    }

    public function testConvertToImportFormat()
    {
        $firstNameVar = new ExtendedMergeVar();
        $lastNameVar = new ExtendedMergeVar();
        $emailVar = new ExtendedMergeVar();
        $nonExistentVar = new ExtendedMergeVar();
        $firstNameVar->setName('fName');
        $lastNameVar->setName('lName');
        $emailVar->setName('email');
        $nonExistentVar->setName('nonExistent');
        $vars = new ArrayCollection(
            array(
                $firstNameVar,
                $lastNameVar,
                $emailVar,
                $nonExistentVar
            )
        );

        $importedRecord = array(
            'e_fName' => 'John',
            'e_lName' => 'Doe',
            'e_email' => 'john.doe@email.com',
            'invalid_var' => 'value',
            'extended_merge_vars' => $vars
        );

        $result = $this->converter->convertToImportFormat($importedRecord);

        $this->assertNotEmpty($result);

        $this->assertArrayHasKey($firstNameVar->getTag(), $result);
        $this->assertArrayHasKey($lastNameVar->getTag(), $result);
        $this->assertArrayHasKey($emailVar->getTag(), $result);
        $this->assertArrayNotHasKey($nonExistentVar->getTag(), $result);

        $this->assertEquals('John', $result[$firstNameVar->getTag()]);
        $this->assertEquals('Doe', $result[$lastNameVar->getTag()]);
        $this->assertEquals('john.doe@email.com', $result[$emailVar->getTag()]);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Is not implemented.
     */
    public function testConvertToExportFormat()
    {
        $this->converter->convertToExportFormat(array());
    }
}
