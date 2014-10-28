<?php

namespace OroCRM\Bundle\MailChimpBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use OroCRM\Bundle\MailChimpBundle\Entity\Repository\StaticSegmentRepository;

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
        $iterator = $this->getStaticSegmentRepository()->getStaticSegmentsWithDynamicMarketingList($segments);
        $jobExecutor = $this->getJobExecutor();

        foreach ($iterator as $segment) {
            $jobExecutor->executeJob(
                ProcessorRegistry::TYPE_IMPORT,
                'mailchimp_marketing_list_subscribe',
                ['import' => ['segment' => $segment]]
            );
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
}
