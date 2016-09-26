<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Provider;

use Oro\Bundle\MailChimpBundle\Provider\ChannelType;

class ChannelTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var ChannelType */
    protected $channel;

    protected function setUp()
    {
        $this->channel = new ChannelType();
    }

    public function testGetLabel()
    {
        $this->assertInstanceOf('Oro\Bundle\IntegrationBundle\Provider\ChannelInterface', $this->channel);
        $this->assertEquals('oro.mailchimp.channel_type.label', $this->channel->getLabel());
    }

    public function testGetIcon()
    {
        $this->assertInstanceOf('Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface', $this->channel);
        $this->assertEquals('bundles/oromailchimp/img/freddie.ico', $this->channel->getIcon());
    }
}
