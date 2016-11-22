<?php
namespace Oro\Bundle\DotmailerBundle\Tests\Functional\Async;

use Oro\Bundle\MailChimpBundle\Async\ExportMailChimpProcessor;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class ExportMailChimpProcessorTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initClient();
    }

    public function testCouldBeGetFromContainerAsService()
    {
        $processor = self::getContainer()->get('oro_mailchimp.async.export_mailchimp_processor');

        self::assertInstanceOf(ExportMailChimpProcessor::class, $processor);
    }
}
