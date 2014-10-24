<?php

namespace OroCRM\Bundle\MailChimpBundle\Command;

use OroCRM\Bundle\MailChimpBundle\Entity\Repository\SegmentRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
            ->setDescription('Send email campaigns');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $iterator = $this->getSegmentRepository()->getSegmentsWithDynamicMarketingList();
        $segmentSyncService = $this->getContainer()->get('orocrm_mailchimp.segment.sync');

        foreach ($iterator as $segment) {
            $segmentSyncService->sync($segment);
        }
    }

    /**
     * @return SegmentRepository
     */
    public function getSegmentRepository()
    {
        return $this->getContainer()->get('doctrine')->getRepository(
            $this->getContainer()->getParameter('orocrm_mailchimp.entity.segment.class')
        );
    }
}
