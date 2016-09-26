<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Model\MergeVar;

use Oro\Bundle\MailChimpBundle\Model\MergeVar\MergeVarFields;
use Oro\Bundle\MailChimpBundle\Model\MergeVar\MergeVarInterface;

class MergeVarFieldsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param MergeVarInterface[] $mergeVars
     * @return MergeVarFields
     */
    protected function createMergeVarFields(array $mergeVars)
    {
        return new MergeVarFields($mergeVars);
    }

    public function testGetEmailFieldNotFound()
    {
        $field = $this->getMock('Oro\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarInterface');
        $field->expects($this->once())
            ->method('isEmail')
            ->will($this->returnValue(false));

        $mergeVarFields = $this->createMergeVarFields([$field]);

        $this->assertNull($mergeVarFields->getEmail());
    }

    public function testGetEmailFieldFound()
    {
        $field = $this->getMock('Oro\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarInterface');
        $field->expects($this->once())
            ->method('isEmail')
            ->will($this->returnValue(false));

        $foundField = $this->getMock('Oro\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarInterface');
        $foundField->expects($this->once())
            ->method('isEmail')
            ->will($this->returnValue(true));

        $mergeVarFields = $this->createMergeVarFields([$field, $foundField]);

        $this->assertSame($foundField, $mergeVarFields->getEmail());
    }

    public function testGetPhoneFieldNotFound()
    {
        $field = $this->getMock('Oro\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarInterface');
        $field->expects($this->once())
            ->method('isPhone')
            ->will($this->returnValue(false));

        $mergeVarFields = $this->createMergeVarFields([$field]);

        $this->assertNull($mergeVarFields->getPhone());
    }

    public function testGetPhoneFieldFound()
    {
        $field = $this->getMock('Oro\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarInterface');
        $field->expects($this->once())
            ->method('isPhone')
            ->will($this->returnValue(false));

        $foundField = $this->getMock('Oro\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarInterface');
        $foundField->expects($this->once())
            ->method('isPhone')
            ->will($this->returnValue(true));

        $mergeVarFields = $this->createMergeVarFields([$field, $foundField]);

        $this->assertSame($foundField, $mergeVarFields->getPhone());
    }

    public function testGetFirstNameFieldNotFound()
    {
        $field = $this->getMock('Oro\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarInterface');
        $field->expects($this->once())
            ->method('isFirstName')
            ->will($this->returnValue(false));

        $mergeVarFields = $this->createMergeVarFields([$field]);

        $this->assertNull($mergeVarFields->getFirstName());
    }

    public function testGetFirstNameFieldFound()
    {
        $field = $this->getMock('Oro\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarInterface');
        $field->expects($this->once())
            ->method('isFirstName')
            ->will($this->returnValue(false));

        $foundField = $this->getMock('Oro\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarInterface');
        $foundField->expects($this->once())
            ->method('isFirstName')
            ->will($this->returnValue(true));

        $mergeVarFields = $this->createMergeVarFields([$field, $foundField]);

        $this->assertSame($foundField, $mergeVarFields->getFirstName());
    }

    public function testGetLastNameFieldNotFound()
    {
        $field = $this->getMock('Oro\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarInterface');
        $field->expects($this->once())
            ->method('isLastName')
            ->will($this->returnValue(false));

        $mergeVarFields = $this->createMergeVarFields([$field]);

        $this->assertNull($mergeVarFields->getLastName());
    }

    public function testGetLastNameFieldFound()
    {
        $field = $this->getMock('Oro\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarInterface');
        $field->expects($this->once())
            ->method('isLastName')
            ->will($this->returnValue(false));

        $foundField = $this->getMock('Oro\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarInterface');
        $foundField->expects($this->once())
            ->method('isLastName')
            ->will($this->returnValue(true));

        $mergeVarFields = $this->createMergeVarFields([$field, $foundField]);

        $this->assertSame($foundField, $mergeVarFields->getLastName());
    }
}
