<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Entity;

use OroCRM\Bundle\MailChimpBundle\Entity\Template;
use Symfony\Component\HttpFoundation\ParameterBag;
use OroCRM\Bundle\MailChimpBundle\Entity\MailChimpTransportSettings;

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
            ['channel', $this->getMock('Oro\\Bundle\\MailChimpBundle\\Entity\\Channel')],
            ['template', $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Entity\\Template')],
        ];
    }

    public function testSettingsBag()
    {
        $channel = $this->getMock('Oro\\Bundle\\MailChimpBundle\\Entity\\Channel');
        $template = new Template();
        $this->target->setChannel($channel);
        $this->target->setTemplate($template);
        $this->assertNotNull($this->target->getChannel());
        $this->assertNotNull($this->target->getTemplate());
        $this->assertEquals(
            new ParameterBag(['channel' => $channel, 'template' => $template]),
            $this->target->getSettingsBag()
        );

        // same any time
        $this->assertEquals(
            new ParameterBag(['channel' => $channel, 'template' => $template]),
            $this->target->getSettingsBag()
        );
    }
}
