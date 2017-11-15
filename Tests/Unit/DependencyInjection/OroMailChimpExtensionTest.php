<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\MailChimpBundle\DependencyInjection\OroMailChimpExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroMailChimpExtensionTest extends ExtensionTestCase
{
    public function testLoad()
    {
        $this->loadExtension(new OroMailChimpExtension());

        $expectedDefinitions = [
            'oro_mailchimp.client.factory',
            'oro_mailchimp.transport.mailchimp',
            'oro_mailchimp.model.field_helper',
        ];
        $this->assertDefinitionsLoaded($expectedDefinitions);
    }

    public function testSetStaledTimeoutForExportJob()
    {
        $originalConfig = [
            'oro_message_queue' => [
                0 =>[
                    'time_before_stale' => [
                        'jobs' => [],
                    ],
                ],
            ]
        ];

        $expectedConfig = [
            'oro_message_queue' => [
                0 =>[
                    'time_before_stale' => [
                        'jobs' => [
                            'oro_mailchimp:export_mailchimp' => 3600
                        ],
                    ],
                ],
                1 =>[
                    'time_before_stale' => [
                        'jobs' => [],
                    ],
                ],
            ]
        ];

        $containerBuilder = new ContainerBuilder();
        $reflectionObject = new \ReflectionObject($containerBuilder);
        $extensionConfigs = $reflectionObject->getProperty('extensionConfigs');
        $extensionConfigs->setAccessible(true);
        $extensionConfigs->setValue($containerBuilder, $originalConfig);

        $extension = new OroMailChimpExtension();
        $extension->prepend($containerBuilder);


        $this->assertEquals($expectedConfig, $extensionConfigs->getValue($containerBuilder));
    }
}
