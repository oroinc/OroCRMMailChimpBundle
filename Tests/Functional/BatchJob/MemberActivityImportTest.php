<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\BatchJob;

use Oro\Bundle\ImportExportBundle\Job\JobResult;
use Oro\Bundle\PlatformBundle\Yaml\Yaml;

use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\MailChimpBundle\Provider\Connector\MemberActivityConnector;

/**
 * @dbIsolation
 */
class MemberActivityImportTest extends WebTestCase
{
    /**
     * @var JobExecutor
     */
    protected $jobExecutor;

    public function setUp()
    {
        if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
            $this->markTestSkipped('Due to BAP-10174');
        }

        $this->initClient();

        $this->jobExecutor = $this->getContainer()->get('oro_importexport.job_executor');

        $this->getContainer()
            ->get('orocrm_mailchimp.client.factory')
            ->setClientClass('OroCRM\Bundle\MailChimpBundle\Tests\Functional\Stub\MailChimpClientStub');

        $this->loadFixtures(
            [
                'OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadCampaignData',
                'OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures\LoadMemberData',
            ]
        );
    }

    public function testRunJob()
    {
        $jobResult = $this->jobExecutor->executeJob(
            ProcessorRegistry::TYPE_IMPORT,
            MemberActivityConnector::JOB_IMPORT,
            [
                ProcessorRegistry::TYPE_IMPORT => [
                    'channel' => $this->getReference('mailchimp:channel_1')->getId(),
                    'channelType' => $this->getReference('mailchimp:channel_1')->getType(),
                    'processorAlias' => 'test'
                ]
            ]
        );

        $this->assertTrue($jobResult->isSuccessful(), implode(',', $jobResult->getFailureExceptions()));
        $this->assertEquals(
            0,
            $jobResult->getContext()->getErrorEntriesCount(),
            implode(', ', $jobResult->getContext()->getErrors())
        );
        $this->assertDatabaseContent($jobResult);
    }

    /**
     * @param JobResult $jobResult
     */
    protected function assertDatabaseContent(JobResult $jobResult)
    {
        $fixtures = new \RecursiveDirectoryIterator(
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Stub' . DIRECTORY_SEPARATOR . 'fixtures',
            \RecursiveDirectoryIterator::SKIP_DOTS
        );

        $campaignRepo = $this->getContainer()->get('doctrine')->getRepository('OroCRMMailChimpBundle:Campaign');
        $repository = $this->getContainer()->get('doctrine')->getRepository('OroCRMMailChimpBundle:MemberActivity');

        $addCount = 0;
        $fullCount = 0;
        foreach ($fixtures as $file) {
            $data = Yaml::parse(file_get_contents($file->getPathName()));
            $addCount += $data['addCount'];
            $fullCount += $data['fullCount'];

            foreach ($data['database'] as $criteria) {
                $campaign = $campaignRepo->findOneBy(['originId' => $criteria['campaign']]);
                $criteria['campaign'] = $campaign->getId();
                if (!empty($criteria['activityTime'])) {
                    $criteria['activityTime'] = new \DateTime($criteria['activityTime'], new \DateTimeZone('UTC'));
                }

                $result = $repository->findBy($criteria);

                $this->assertCount(1, $result, $file->getFileName());
            }
        }

        $this->assertEquals($addCount, $jobResult->getContext()->getAddCount());
        $this->assertCount($fullCount, $repository->findAll());
    }
}
