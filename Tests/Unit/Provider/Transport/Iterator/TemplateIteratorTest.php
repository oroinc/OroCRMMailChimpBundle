<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Provider\Transport\Iterator;

use OroCRM\Bundle\MailChimpBundle\Entity\Template;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator\TemplateIterator;

class TemplateIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $client;

    /**
     * @var \Iterator
     */
    protected $iterator;

    protected function setUp()
    {
        $this->client = $this->getMockBuilder('OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient')
            ->disableOriginalConstructor()
            ->setMethods(['getTemplates'])
            ->getMock();

        $this->iterator = new TemplateIterator($this->client);
    }

    public function testIterator()
    {
        $rawTemplates = [
            'user' => [
                ['id' => 1, 'name' => 'template1'],
                ['id' => 2, 'name' => 'template2'],
            ],
            'gallery' => [
                ['id' => 3, 'name' => 'template3'],
            ]
        ];
        $expected = [
            ['origin_id' => 1, 'name' => 'template1', 'type' => 'user'],
            ['origin_id' => 2, 'name' => 'template2', 'type' => 'user'],
            ['origin_id' => 3, 'name' => 'template3', 'type' => 'gallery'],
        ];

        $this->client->expects($this->once())
            ->method('getTemplates')
            ->with($this->isType('array'))
            ->will($this->returnValue($rawTemplates));

        $actual = [];
        foreach ($this->iterator as $key => $value) {
            $actual[$key] = $value;
        }

        $this->assertEquals($expected, $actual);
    }
}
