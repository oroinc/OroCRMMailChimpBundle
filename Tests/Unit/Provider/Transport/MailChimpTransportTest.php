<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Provider\Transport;

use OroCRM\Bundle\MailChimpBundle\Entity\MailChimpTransport as MailChimpTransportEntity;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\MailChimpTransport;

class MailChimpTransportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientFactory;

    /**
     * @var MailChimpTransport
     */
    protected $transport;

    protected function setUp()
    {
        $this->clientFactory = $this->getMockBuilder(
            'OroCRM\\Bundle\\MailChimpBundle\\Provider\\Transport\\MailChimpClientFactory'
        )->disableOriginalConstructor()->getMock();

        $this->transport = new MailChimpTransport($this->clientFactory);
    }

    public function testGetSettingsEntityFQCN()
    {
        $this->assertInstanceOf($this->transport->getSettingsEntityFQCN(), new MailChimpTransportEntity());
    }

    public function testGetLabel()
    {
        $this->assertEquals('orocrm.mailchimp.integration_transport.label', $this->transport->getLabel());
    }

    public function testGetSettingsFormType()
    {
        $this->assertEquals(
            'orocrm_mailchimp_integration_transport_setting_type',
            $this->transport->getSettingsFormType()
        );
    }
}
