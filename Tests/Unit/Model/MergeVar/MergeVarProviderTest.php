<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Model\MergeVar;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;
use OroCRM\Bundle\MailChimpBundle\Model\MergeVar\MergeVar;
use OroCRM\Bundle\MailChimpBundle\Model\MergeVar\MergeVarFields;
use OroCRM\Bundle\MailChimpBundle\Model\MergeVar\MergeVarProvider;

class MergeVarProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MergeVarProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new MergeVarProvider();
    }

    public function testGetMergeVarsFieldsWorks()
    {
        $mergeVarConfig = [['name' => 'foo', 'type' => 'email'], ['name' => 'bar', 'type' => 'text']];

        $subscribersList = new SubscribersList();
        $subscribersList->setMergeVarConfig($mergeVarConfig);

        $mergeVarFields = $this->provider->getMergeVarFields($subscribersList);

        $this->assertEquals(
            new MergeVarFields([new MergeVar($mergeVarConfig[0]), new MergeVar($mergeVarConfig[1])]),
            $mergeVarFields
        );

        $this->assertSame($mergeVarFields, $subscribersList->getMergeVarFields());
    }

    public function testGetMergeVarsFieldsWithEmptyConfigWorks()
    {
        $subscribersList = new SubscribersList();
        $subscribersList->setMergeVarConfig([]);

        $mergeVarFields = $this->provider->getMergeVarFields($subscribersList);

        $this->assertEquals(
            new MergeVarFields([]),
            $mergeVarFields
        );

        $this->assertSame($mergeVarFields, $subscribersList->getMergeVarFields());
    }

    public function testAssignMergeVarValuesWorks()
    {
        $emailField = $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarInterface');
        $emailField->expects($this->atLeastOnce())
            ->method('getName')
            ->will($this->returnValue('Email Address'));

        $phoneField = $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarInterface');
        $phoneField->expects($this->atLeastOnce())
            ->method('getName')
            ->will($this->returnValue('Phone'));

        $firstNameField = $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarInterface');
        $firstNameField->expects($this->atLeastOnce())
            ->method('getName')
            ->will($this->returnValue('First Name'));

        $lastNameField = $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarInterface');
        $lastNameField->expects($this->atLeastOnce())
            ->method('getName')
            ->will($this->returnValue('Last Name'));

        $mergeVarFields = $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarFieldsInterface');
        $mergeVarFields->expects($this->once())
            ->method('getEmail')
            ->will($this->returnValue($emailField));

        $mergeVarFields->expects($this->once())
            ->method('getPhone')
            ->will($this->returnValue($phoneField));

        $mergeVarFields->expects($this->once())
            ->method('getFirstName')
            ->will($this->returnValue($firstNameField));

        $mergeVarFields->expects($this->once())
            ->method('getLastName')
            ->will($this->returnValue($lastNameField));

        $email = 'test@example.com';
        $phone = '333-555-7777';
        $firstName = 'John';
        $lastName = 'Doe';

        $member = new Member();
        $member->setMergeVarValues(
            [
                'Email Address' => $email,
                'Phone' => $phone,
                'First Name' => $firstName,
                'Last Name' => $lastName,
            ]
        );
        $this->provider->assignMergeVarValues($member, $mergeVarFields);

        $this->assertEquals($email, $member->getEmail());
        $this->assertEquals($phone, $member->getPhone());
        $this->assertEquals($firstName, $member->getFirstName());
        $this->assertEquals($lastName, $member->getLastName());
    }

    public function testAssignMergeVarValuesWorksWithEmptyValues()
    {
        $emailField = $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarInterface');
        $emailField->expects($this->atLeastOnce())
            ->method('getName')
            ->will($this->returnValue('Email Address'));

        $phoneField = $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarInterface');
        $phoneField->expects($this->atLeastOnce())
            ->method('getName')
            ->will($this->returnValue('Phone'));

        $firstNameField = $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarInterface');
        $firstNameField->expects($this->atLeastOnce())
            ->method('getName')
            ->will($this->returnValue('First Name'));

        $lastNameField = $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarInterface');
        $lastNameField->expects($this->atLeastOnce())
            ->method('getName')
            ->will($this->returnValue('Last Name'));

        $mergeVarFields = $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarFieldsInterface');
        $mergeVarFields->expects($this->once())
            ->method('getEmail')
            ->will($this->returnValue($emailField));

        $mergeVarFields->expects($this->once())
            ->method('getPhone')
            ->will($this->returnValue($phoneField));

        $mergeVarFields->expects($this->once())
            ->method('getFirstName')
            ->will($this->returnValue($firstNameField));

        $mergeVarFields->expects($this->once())
            ->method('getLastName')
            ->will($this->returnValue($lastNameField));

        $member = new Member();
        $member->setMergeVarValues([]);
        $this->provider->assignMergeVarValues($member, $mergeVarFields);

        $this->assertNull($member->getEmail());
        $this->assertNull($member->getPhone());
        $this->assertNull($member->getFirstName());
        $this->assertNull($member->getLastName());
    }

    public function testAssignMergeVarValuesWorksWithEmptyFields()
    {
        $mergeVarFields = $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarFieldsInterface');
        $mergeVarFields->expects($this->once())
            ->method('getEmail')
            ->will($this->returnValue(null));

        $mergeVarFields->expects($this->once())
            ->method('getPhone')
            ->will($this->returnValue(null));

        $mergeVarFields->expects($this->once())
            ->method('getFirstName')
            ->will($this->returnValue(null));

        $mergeVarFields->expects($this->once())
            ->method('getLastName')
            ->will($this->returnValue(null));

        $email = 'test@example.com';
        $phone = '333-555-7777';
        $firstName = 'John';
        $lastName = 'Doe';

        $member = new Member();
        $member->setMergeVarValues(
            [
                'Email Address' => $email,
                'Phone' => $phone,
                'First Name' => $firstName,
                'Last Name' => $lastName,
            ]
        );
        $this->provider->assignMergeVarValues($member, $mergeVarFields);

        $this->assertNull($member->getEmail());
        $this->assertNull($member->getPhone());
        $this->assertNull($member->getFirstName());
        $this->assertNull($member->getLastName());
    }
}
