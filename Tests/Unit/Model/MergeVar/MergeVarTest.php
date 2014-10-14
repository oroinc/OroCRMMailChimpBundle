<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Model\MergeVar;

use OroCRM\Bundle\MailChimpBundle\Model\MergeVar\MergeVar;

class MergeVarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider mergeVarDataProvider
     * @param array $data
     */
    public function testGetName(array $data)
    {
        $mergeVar = $this->createMergeVarObject($data);
        $this->assertEquals($data['name'], $mergeVar->getName());
    }

    /**
     * @dataProvider mergeVarDataProvider
     * @param array $data
     */
    public function testGetFieldType(array $data)
    {
        $mergeVar = $this->createMergeVarObject($data);
        $this->assertEquals($data['field_type'], $mergeVar->getFieldType());
    }

    /**
     * @dataProvider mergeVarDataProvider
     * @param array $data
     */
    public function testGetTag(array $data)
    {
        $mergeVar = $this->createMergeVarObject($data);
        $this->assertEquals($data['tag'], $mergeVar->getTag());
    }

    /**
     * @dataProvider mergeVarDataProvider
     * @param array $data
     */
    public function testIsFirstName(array $data)
    {
        $mergeVar = $this->createMergeVarObject($data);
        $this->assertEquals($data['tag'] == 'FNAME', $mergeVar->isFirstName());
    }

    /**
     * @dataProvider mergeVarDataProvider
     * @param array $data
     */
    public function testIsLastName(array $data)
    {
        $mergeVar = $this->createMergeVarObject($data);
        $this->assertEquals($data['tag'] == 'LNAME', $mergeVar->isLastName());
    }

    /**
     * @dataProvider mergeVarDataProvider
     * @param array $data
     */
    public function testIsEmail(array $data)
    {
        $mergeVar = $this->createMergeVarObject($data);
        $this->assertEquals($data['tag'] == 'EMAIL', $mergeVar->isEmail());
    }

    /**
     * @dataProvider mergeVarDataProvider
     * @param array $data
     */
    public function testIsPhone(array $data)
    {
        $mergeVar = $this->createMergeVarObject($data);
        $this->assertEquals($data['field_type'] == 'phone', $mergeVar->isPhone());
    }

    /**
     * @return array
     */
    public function mergeVarDataProvider()
    {
        return [
            [
                'data' => [
                    'name' => 'Email Address 111',
                    'req' => true,
                    'field_type' => 'email',
                    'public' => true,
                    'show' => true,
                    'order' => '1',
                    'default' => '',
                    'helptext' => '',
                    'size' => '25',
                    'tag' => 'EMAIL',
                    'id' => 0,
                ]
            ],
            [
                'data' => [
                    'name' => 'First Name',
                    'req' => false,
                    'field_type' => 'text',
                    'public' => true,
                    'show' => true,
                    'order' => '2',
                    'default' => '',
                    'helptext' => '',
                    'size' => '25',
                    'tag' => 'FNAME',
                    'id' => 1,
                ]
            ],
            [
                'data' => [
                    'name' => 'Last Name',
                    'req' => false,
                    'field_type' => 'text',
                    'public' => true,
                    'show' => true,
                    'order' => '3',
                    'default' => '',
                    'helptext' => '',
                    'size' => '25',
                    'tag' => 'LNAME',
                    'id' => 2,
                ]
            ],
            [
                'data' => [
                    'name' => 'Phone',
                    'req' => false,
                    'field_type' => 'phone',
                    'public' => true,
                    'show' => true,
                    'order' => '4',
                    'default' => '',
                    'helptext' => '',
                    'size' => '25',
                    'phoneformat' => '',
                    'tag' => 'MMERGE3',
                    'id' => 3,
                ]
            ],
            [
                'data' => [
                    'name' => 'Untitled',
                    'req' => false,
                    'field_type' => 'zip',
                    'public' => true,
                    'show' => true,
                    'order' => '5',
                    'default' => '',
                    'helptext' => '',
                    'size' => '25',
                    'tag' => 'MMERGE4',
                    'id' => 4,
                ],
            ],
        ];
    }

    protected function createMergeVarObject(array $data)
    {
        return new MergeVar($data);
    }
}
