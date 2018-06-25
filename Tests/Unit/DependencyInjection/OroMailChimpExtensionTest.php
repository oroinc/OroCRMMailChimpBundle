<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\MailChimpBundle\DependencyInjection\OroMailChimpExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroMailChimpExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testLoad()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|ContainerBuilder $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)
                          ->disableOriginalConstructor()
                          ->getMock();

        $container->expects($this->once())
                  ->method('prependExtensionConfig')
                  ->with(OroMailChimpExtension::ALIAS, $this->isType('array'));

        $extension = new OroMailChimpExtension();
        $extension->load([], $container);
    }

    public function testGetAlias()
    {
        $extension = new OroMailChimpExtension();

        $this->assertEquals(OroMailChimpExtension::ALIAS, $extension->getAlias());
    }
}
