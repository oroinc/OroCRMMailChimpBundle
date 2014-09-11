<?php
namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Entity;

use Symfony\Component\HttpFoundation\ParameterBag;

use OroCRM\Bundle\MailChimpBundle\Entity\MailChimpTransport;

class MailChimpTransportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MailChimpTransport
     */
    protected $entity;

    protected function setUp()
    {
        $this->entity = new MailChimpTransport();
    }

    public function testApiKey()
    {
        $apiKey = uniqid();
        $this->entity->setApiKey($apiKey);

        $this->assertEquals($apiKey, $this->entity->getApiKey());
    }

    public function testSettingsBag()
    {
        $apiKey = uniqid();
        $this->entity->setApiKey($apiKey);
        $this->assertNotNull($this->entity->getApiKey());
        $this->assertEquals(new ParameterBag(['apiKey' => $apiKey]), $this->entity->getSettingsBag());

        // same any time
        $this->assertEquals(new ParameterBag(['apiKey' => $apiKey]), $this->entity->getSettingsBag());
    }
}
