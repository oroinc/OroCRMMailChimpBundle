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
            ['createRequest'],
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

        $expectedMethod = 'POST';
        $expectedUrl = sprintf(
            'https://%s.api.mailchimp.com/export/%s/%s/',
            self::DC,
            MailChimpClient::EXPORT_API_VERSION,
            $methodName
        );
        $expectedHeaders = ['Content-Type' => 'application/json'];
        $expectedRequestEntity = json_encode(array_merge(['apikey' => self::API_KEY], $parameters));

        $request = $this->getMock('Guzzle\\Http\\Message\\RequestInterface');

        $this->client->expects($this->once())
            ->method('createRequest')
            ->with($expectedMethod, $expectedUrl, $expectedHeaders, $expectedRequestEntity)
            ->will($this->returnValue($request));

        $response = $this->getMockBuilder('Guzzle\\Http\\Message\\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $request->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $response->expects($this->once())
            ->method('getContentType')
            ->will($this->returnValue('text/html'));

        $this->assertEquals($response, $this->client->export($methodName, $parameters));
    }

    public function testExportFailsWithInvalidResponse()
    {
        $methodName = 'list';
        $parameters = ['id' => 123456];
        $expectedUrl = sprintf(
            'https://%s.api.mailchimp.com/export/%s/%s/',
            self::DC,
            MailChimpClient::EXPORT_API_VERSION,
            $methodName
        );

        $request = $this->getMock('Guzzle\\Http\\Message\\RequestInterface');

        $this->client->expects($this->once())
            ->method('createRequest')
            ->will($this->returnValue($request));

        $response = $this->getMockBuilder('Guzzle\\Http\\Message\\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $request->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $request->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue($expectedUrl));

        $response->expects($this->once())
            ->method('getStatusCode')
            ->will($this->returnValue(200));

        $response->expects($this->once())
            ->method('getReasonPhrase')
            ->will($this->returnValue('OK'));

        $response->expects($this->atLeastOnce())
            ->method('getContentType')
            ->will($this->returnValue('application/json'));

        $response->expects($this->once())
            ->method('getBody')
            ->with(true)
            ->will($this->returnValue('{"error":"API Key can not be blank","code":104}'));

        $this->setExpectedException(
            'OroCRM\\Bundle\\MailChimpBundle\\Provider\\Transport\\Exception\\BadResponseException',
            'Invalid response, expected content type is text/html' . PHP_EOL .
            '[status code] 200' . PHP_EOL .
            '[reason phrase] OK' . PHP_EOL .
            '[url] https://us9.api.mailchimp.com/export/1.0/list/' . PHP_EOL .
            '[content type] application/json' . PHP_EOL .
            '[response body] {"error":"API Key can not be blank","code":104}'
        );

        $this->assertEquals($response, $this->client->export($methodName, $parameters));
    }
}
