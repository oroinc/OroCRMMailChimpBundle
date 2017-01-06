<?php

namespace Oro\Bundle\MailChimpBundle\Command;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\MailChimpBundle\Async\Topics;
use Oro\Bundle\MailChimpBundle\Entity\Repository\StaticSegmentRepository;
use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;

use Oro\Bundle\MailChimpBundle\Model\StaticSegment\StaticSegmentsMemberStateManager;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class MailChimpExportCommand extends Command implements CronCommandInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '*/5 * * * *';
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        $count = $this->getStaticSegmentRepository()->countStaticSegments();

        return ($count > 0);
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
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

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
            )->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Run sync in force mode'
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $segments = $input->getOption('segments');
        $force = (bool) $input->getOption('force');

        /** @var StaticSegment[] $iterator */
        $iterator = $this->getStaticSegmentRepository()->getStaticSegmentsToSync($segments, null, $force);

        /** @var Integration[] $integrationToSync */
        $integrationToSync   = [];
        $integrationSegments = [];
        foreach ($iterator as $staticSegment) {
            $integration = $staticSegment->getChannel();
            $integrationToSync[$integration->getId()] = $integration;
            $integrationSegments[$integration->getId()][] = $staticSegment->getId();
        }

        $output->writeln('Send export MailChimp message for integration:');
        foreach ($integrationToSync as $integration) {
            $message = new Message();
            $message->setPriority(MessagePriority::VERY_LOW);
            $message->setBody([
                'integrationId' => $integration->getId(),
                'segmentsIds' => $integrationSegments[$integration->getId()]
            ]);

            $output->writeln(sprintf(
                'Integration "%s" and segments "%s"',
                $message->getBody()['integrationId'],
                implode('", "', $message->getBody()['segmentsIds'])
            ));

            $this->getMessageProducer()->send(Topics::EXPORT_MAILCHIMP_SEGMENTS, $message);
        }
    }

    /**
     * @return StaticSegmentRepository
     */
    protected function getStaticSegmentRepository()
    {
        /** @var RegistryInterface $registry */
        $registry = $this->container->get('doctrine');

        return $registry->getRepository(
            $this->container->getParameter('oro_mailchimp.entity.static_segment.class')
        );
    }

    /**
     * @return MessageProducerInterface
     */
    private function getMessageProducer()
    {
        return $this->container->get('oro_message_queue.message_producer');
    }
}
