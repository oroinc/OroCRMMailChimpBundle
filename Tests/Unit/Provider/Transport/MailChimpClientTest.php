<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Provider\Transport;

use Oro\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient;

class MailChimpClientTest extends \PHPUnit_Framework_TestCase
{
    const API_KEY = '3024ddceb22913e9f8ff39fe9be157f6-us9';
    const DC = 'us9';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|MailChimpClient
     */
    protected $client;

    protected function setUp()
    {
        $this->client = $this->getMockBuilder('Oro\Bundle\MailChimpBundle\Provider\Transport\MailChimpClient')
            ->setMethods(['createRequest', 'callExportApi'])
            ->setConstructorArgs([self::API_KEY])
            ->getMock();
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
            'https://%s.api.mailchimp.com/export/%s/%s/',
            self::DC,
            MailChimpClient::EXPORT_API_VERSION,
            $methodName
        );
        $expectedRequestEntity = json_encode(array_merge(['apikey' => self::API_KEY], $parameters));

        $response = $this->getMockBuilder('Guzzle\\Http\\Message\\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $this->client->expects($this->once())
            ->method('callExportApi')
            ->with($expectedUrl, $expectedRequestEntity)
            ->will($this->returnValue($response));

        $response->expects($this->once())
            ->method('getContentType')
            ->will($this->returnValue('text/html'));

        $response->expects($this->once())
            ->method('isSuccessful')
            ->will($this->returnValue(true));

        $this->assertEquals($response, $this->client->export($methodName, $parameters));
    }

    /**
     * @dataProvider invalidResponseDataProvider
     * @param bool $successful
     * @param string $expectedError
     */
    public function testExportFailsWithInvalidResponse($successful, $expectedError)
    {
        $methodName = 'list';
        $parameters = ['id' => 123456];
        $expectedUrl = sprintf(
            'https://%s.api.mailchimp.com/export/%s/%s/',
            self::DC,
            MailChimpClient::EXPORT_API_VERSION,
            $methodName
        );
        $expectedRequestEntity = json_encode(array_merge(['apikey' => self::API_KEY], $parameters));

        $response = $this->getMockBuilder('Guzzle\\Http\\Message\\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $this->client->expects($this->once())
            ->method('callExportApi')
            ->with($expectedUrl, $expectedRequestEntity)
            ->will($this->returnValue($response));

        $response->expects($this->once())
            ->method('isSuccessful')
            ->will($this->returnValue($successful));

        $response->expects($this->once())
            ->method('getStatusCode')
            ->will($this->returnValue(500));

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

        $this->expectException('Oro\\Bundle\\MailChimpBundle\\Provider\\Transport\\Exception\\BadResponseException');
        $this->expectExceptionMessage(
            implode(
                PHP_EOL,
                [
                    $expectedError,
                    '[status code] 500',
                    '[API error code] ',
                    '[reason phrase] OK',
                    '[url] https://us9.api.mailchimp.com/export/1.0/list/',
                    '[request parameters]' . $expectedRequestEntity,
                    '[content type] application/json',
                    '[response body] {"error":"API Key can not be blank","code":104}'
                ]
            )
        );

        $this->client->export($methodName, $parameters);
    }

    public function invalidResponseDataProvider()
    {
        return [
            [true, 'Invalid response, expected content type is text/html'],
            [false, 'Request to MailChimp Export API wasn\'t successfully completed.']
        ];
    }
}
