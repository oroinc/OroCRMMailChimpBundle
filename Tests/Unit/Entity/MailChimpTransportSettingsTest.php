<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Entity;

use Symfony\Component\HttpFoundation\ParameterBag;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MailChimpBundle\Entity\Template;
use Oro\Bundle\MailChimpBundle\Entity\MailChimpTransportSettings;

class MailChimpTransportSettingsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MailChimpTransportSettings
     */
    protected $target;

    public function setUp()
    {
        $this->target = new MailChimpTransportSettings();
    }

    /**
     * @param string $property
     * @param mixed $value
     *
     * @dataProvider settersAndGettersDataProvider
     */
    public function testSettersAndGetters($property, $value)
    {
        $method = 'set' . ucfirst($property);
        $result = $this->target->$method($value);

        $this->assertInstanceOf(get_class($this->target), $result);
        $this->assertEquals($value, $this->target->{'get' . $property}());
    }

    /**
     * @return array
     */
    public function settersAndGettersDataProvider()
    {
        return [
            ['channel', $this->createMock('Oro\\Bundle\\IntegrationBundle\\Entity\\Channel')],
            ['template', $this->createMock('Oro\\Bundle\\MailChimpBundle\\Entity\\Template')],
        ];
    }

    public function testReceiveActivities()
    {
        $this->assertTrue($this->target->isReceiveActivities());
        $this->target->setReceiveActivities(false);
        $this->assertFalse($this->target->isReceiveActivities());
    }

    public function testSettingsBag()
    {
        /** @var Channel|\PHPUnit_Framework_MockObject_MockObject $channel */
        $channel = $this->createMock('Oro\\Bundle\\IntegrationBundle\\Entity\\Channel');
        $template = new Template();
        $this->target->setChannel($channel);
        $this->target->setTemplate($template);
        $this->target->setReceiveActivities(true);
        $this->assertNotNull($this->target->getChannel());
        $this->assertNotNull($this->target->getTemplate());

        $expectedSettings = [
            'channel' => $channel,
            'receiveActivities' => true
            // 'template' => $template
        ];
        $this->assertEquals(
            new ParameterBag($expectedSettings),
            $this->target->getSettingsBag()
        );
    }
}
