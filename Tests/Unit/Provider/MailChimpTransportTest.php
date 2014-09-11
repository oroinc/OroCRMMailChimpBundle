<?php
namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Provider;

use OroCRM\Bundle\MailChimpBundle\Entity\MailChimpTransport as MailChimpTransportEntity;
use OroCRM\Bundle\MailChimpBundle\Provider\MailChimpTransport;

class MailChimpTransportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MailChimpTransport
     */
    protected $transport;

    protected function setUp()
    {
        $this->transport = new MailChimpTransport();
    }

    public function testGetSettingsEntityFQCN()
    {
        $this->assertInstanceOf($this->transport->getSettingsEntityFQCN(), new MailChimpTransportEntity());
    }
}
