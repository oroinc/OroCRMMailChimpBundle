<?php

namespace OroCRM\Bundle\MailChimpBundle\Command;

use OroCRM\Bundle\MailChimpBundle\Provider\Connector\MemberConnector;
use OroCRM\Bundle\MailChimpBundle\Provider\Connector\StaticSegmentConnector;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\IntegrationBundle\Provider\ReverseSyncProcessor;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Entity\Repository\StaticSegmentRepository;
use OroCRM\Bundle\MailChimpBundle\Model\StaticSegment\StaticSegmentAwareInterface;
use OroCRM\Bundle\MailChimpBundle\Model\StaticSegment\StaticSegmentsMemberStateManager;

class SynchronizeSegmentsCommand extends ContainerAwareCommand implements CronCommandInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '*/1 * * * *';
    }

    /**
     * @var JobExecutor
     */
    protected $jobExecutor;

    /**
     * @var StaticSegmentsMemberStateManager
     */
    protected $staticSegmentStateManager;

    /**
     * @var StaticSegmentsMemberStateManager
     */
    protected $reverseSyncProcessor;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('oro:cron:mailchimp:sync-segment')
            ->setDescription('Synchronize static segments with MailChimp')
            ->addOption(
                'segments',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'MailChimp static Segments to sync'
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $segments = $input->getOption('segments');
        /** @var StaticSegment[] $iterator */
        $iterator = $this->getStaticSegmentRepository()->getStaticSegmentsWithDynamicMarketingList($segments);
        $integrationsToExport = [];

        $jobs = [
            'mailchimp_marketing_list_members_sync',
            'mailchimp_static_segment_member_add_state',
            'mailchimp_static_segment_member_remove_state',
        ];

        foreach ($iterator as $staticSegment) {
            $output->writeln(sprintf('<info>Process Static Segment #%s:</info>', $staticSegment->getId()));
            foreach ($jobs as $jobName) {
                $output->writeln(sprintf('    %s', $jobName));
                $jobResult = $this->getJobExecutor()->executeJob(
                    ProcessorRegistry::TYPE_IMPORT,
                    $jobName,
                    [ProcessorRegistry::TYPE_IMPORT => [StaticSegmentAwareInterface::OPTION_SEGMENT => $staticSegment]]
                );
                if (!$jobResult->isSuccessful()) {
                    $output->writeln($jobResult->getFailureExceptions());
                }
            }

            $integrationsToExport[$staticSegment->getChannel()->getId()] = $staticSegment->getChannel();

            $this->getStaticSegmentStateManager()->drop($staticSegment);
        }

        $exportJobs = [
            MemberConnector::TYPE => MemberConnector::JOB_EXPORT,
            StaticSegmentConnector::TYPE => StaticSegmentConnector::JOB_EXPORT
        ];

        foreach ($exportJobs as $type => $jobName) {
            $output->writeln(sprintf('    %s', $jobName));
            foreach ($integrationsToExport as $integrationToExport) {
                $this->getReverseSyncProcessor()->process($integrationToExport, $type, []);
            }
        }
    }

    /**
     * @return StaticSegmentRepository
     */
    protected function getStaticSegmentRepository()
    {
        return $this->getContainer()->get('doctrine')->getRepository(
            $this->getContainer()->getParameter('orocrm_mailchimp.entity.static_segment.class')
        );
    }

    /**
     * @return JobExecutor
     */
    protected function getJobExecutor()
    {
        if (!$this->jobExecutor) {
            $this->jobExecutor = $this->getContainer()->get('oro_importexport.job_executor');
        }

        return $this->jobExecutor;
    }

    /**
     * @return StaticSegmentsMemberStateManager
     */
    protected function getStaticSegmentStateManager()
    {
        if (!$this->staticSegmentStateManager) {
            $this->staticSegmentStateManager = $this->getContainer()->get(
                'orocrm_mailchimp.static_segment_manager.state_mamanger'
            );
        }

        return $this->staticSegmentStateManager;
    }

    /**
     * @return ReverseSyncProcessor
     */
    protected function getReverseSyncProcessor()
    {
        if (!$this->reverseSyncProcessor) {
            $this->reverseSyncProcessor = $this->getContainer()->get('oro_integration.reverse_sync.processor');
        }

        return $this->reverseSyncProcessor;
    }
}
