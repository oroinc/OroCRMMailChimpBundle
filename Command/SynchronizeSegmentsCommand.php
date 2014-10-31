<?php

namespace OroCRM\Bundle\MailChimpBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
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
        return '*/5 * * * *';
    }

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
        $jobExecutor = $this->getJobExecutor();
        $stateManager = $this->getStaticSegmentStateManager();

        $jobs = [
            'mailchimp_marketing_list_subscribe' => ProcessorRegistry::TYPE_IMPORT,
            'mailchimp_static_segment_member_add_state' => ProcessorRegistry::TYPE_IMPORT,
            'mailchimp_static_segment_member_remove_state' => ProcessorRegistry::TYPE_IMPORT,
            'mailchimp_member_export' => ProcessorRegistry::TYPE_EXPORT,
            'mailchimp_static_segment_export' => ProcessorRegistry::TYPE_EXPORT,
        ];

        foreach ($iterator as $staticSegment) {
            $output->writeln(sprintf('<info>Process Static Segment #%s:</info>', $staticSegment->getId()));
            foreach ($jobs as $job => $type) {
                $output->writeln($job);
                $jobResult = $jobExecutor->executeJob(
                    $type,
                    $job,
                    [$type => [StaticSegmentAwareInterface::OPTION_SEGMENT => $staticSegment]]
                );
                if (!$jobResult->isSuccessful()) {
                    $output->writeln($jobResult->getFailureExceptions());
                }
            }

            $stateManager->drop($staticSegment);
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
        return $this->getContainer()->get('oro_importexport.job_executor');
    }

    /**
     * @return StaticSegmentsMemberStateManager
     */
    protected function getStaticSegmentStateManager()
    {
        return $this->getContainer()->get('orocrm_mailchimp.static_segment_manager.state_mamanger');
    }
}
