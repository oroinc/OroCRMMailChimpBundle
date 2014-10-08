<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Provider\Transport;

use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

class MailChimpClientTest extends \PHPUnit_Framework_TestCase
{
    const API_KEY = '3024ddceb22913e9f8ff39fe9be157f6-us9';
    const DC = 'us9';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $client;

    protected function setUp()
    {
        $this->client = $this->getMock(
            'OroCRM\\Bundle\\MailChimpBundle\\Provider\\Transport\\MailChimpClient',
            ['get'],
            [self::API_KEY]
        );
    }

    public function testConstructorWorks()
    {
        $this->assertAttributeEquals(self::API_KEY, 'apiKey', $this->client);
    }

    public function testExportWorks()
    {
        $methodName = 'list';
        $parameters = ['id' => 123456];

        $expectedUrl = sprintf(
            'https://%s.api.mailchimp.com/export/%s/%s',
            self::DC,
            MailChimpClient::EXPORT_API_VERSION,
            $methodName
        );
        $expectedParameters = array_merge(['api_key' => self::API_KEY], $parameters);

        $request = $this->getMock('Guzzle\\Http\\Message\\RequestInterface');

        $this->client->expects($this->once())
            ->method('get')
            ->with($expectedUrl, $expectedParameters)
            ->will($this->returnValue($request));

        $response = $this->getMockBuilder('Guzzle\\Http\\Message\\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $request->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $this->assertEquals($response, $this->client->export($methodName, $parameters));
    }
}
