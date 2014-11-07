<?php

namespace OroCRM\Bundle\MailChimpBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\IntegrationBundle\Provider\ReverseSyncProcessor;
use OroCRM\Bundle\MailChimpBundle\Entity\Repository\StaticSegmentRepository;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Model\StaticSegment\StaticSegmentsMemberStateManager;
use OroCRM\Bundle\MailChimpBundle\Provider\Connector\MemberConnector;
use OroCRM\Bundle\MailChimpBundle\Provider\Connector\StaticSegmentConnector;

class MailChimpExportCommand extends ContainerAwareCommand implements CronCommandInterface
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
    protected $reverseSyncProcessor;

    /**
     * @var StaticSegmentsMemberStateManager
     */
    protected $staticSegmentStateManager;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('oro:cron:mailchimp:export')
            ->setDescription('Export members and static segments to MailChimp')
            ->addOption(
                'segments',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'MailChimp static StaticSegments to sync'
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $segments = $input->getOption('segments');
        /** @var StaticSegment[] $iterator */
        $iterator = $this->getStaticSegmentRepository()->getStaticSegmentsToSync($segments);

        $exportJobs = [
            MemberConnector::TYPE => MemberConnector::JOB_EXPORT,
            StaticSegmentConnector::TYPE => StaticSegmentConnector::JOB_EXPORT
        ];

        /* @todo: get rid of flushes */
        $doctrineHelper = $this->getContainer()->get('oro_entity.doctrine_helper');
        foreach ($iterator as $staticSegment) {
            $em = $doctrineHelper->getEntityManager($staticSegment);
            $staticSegment->setSyncStatus(StaticSegment::STATUS_IN_PROGRESS);
            $em->persist($staticSegment);
            $em->flush($staticSegment);

            $channel = $staticSegment->getChannel();
            $output->writeln(sprintf('<info>Channel #%s:</info>', $channel->getId()));
            foreach ($exportJobs as $type => $jobName) {
                $output->writeln(sprintf('    %s', $jobName));
                $this->getReverseSyncProcessor()->process($channel, $type, []);
            }

            $this->getStaticSegmentStateManager()->handleDroppedMembers($staticSegment);


            $staticSegment = $doctrineHelper->getEntity(
                $doctrineHelper->getEntityClass($staticSegment),
                $staticSegment->getId()
            );

            $staticSegment
                ->setSyncStatus(StaticSegment::STATUS_SYNCED)
                ->setLastSynced(new \DateTime());

            $em->persist($staticSegment);
            $em->flush($staticSegment);
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
     * @return ReverseSyncProcessor
     */
    protected function getReverseSyncProcessor()
    {
        if (!$this->reverseSyncProcessor) {
            $this->reverseSyncProcessor = $this->getContainer()->get('oro_integration.reverse_sync.processor');
        }

        return $this->reverseSyncProcessor;
    }

    /**
     * @return StaticSegmentsMemberStateManager
     */
    protected function getStaticSegmentStateManager()
    {
        if (!$this->staticSegmentStateManager) {
            $this->staticSegmentStateManager = $this->getContainer()->get(
                'orocrm_mailchimp.static_segment_manager.state_manager'
            );
        }

        return $this->staticSegmentStateManager;
    }
}
