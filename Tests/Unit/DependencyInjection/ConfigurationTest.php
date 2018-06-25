<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\MailChimpBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();

        $this->assertInstanceOf(
            TreeBuilder::class,
            $configuration->getConfigTreeBuilder()
        );
    }

    /**
     * @dataProvider processConfigurationDataProvider
     *
     * @param array $configs
     * @param array $expected
     */
    public function testProcessConfiguration(array $configs, array $expected)
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $this->assertEquals($expected, $processor->processConfiguration($configuration, $configs));
    }

    /**
     * @return array
     */
    public function processConfigurationDataProvider()
    {
        return [
            'empty' => [
                'configs' => [[]],
                'expected' => [
                    'settings' => [
                        'resolved' => 1,
                        'static_segment_sync_mode' => [
                            'value' => 'on_update',
                            'scope' => 'app',
                        ],
                    ],
                ],
            ],
        ];
    }
}
