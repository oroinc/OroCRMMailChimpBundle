<?php

namespace Oro\Bundle\MailChimpBundle\Command;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Bundle\MailChimpBundle\Async\Topics;
use Oro\Bundle\MailChimpBundle\Entity\Repository\StaticSegmentRepository;
use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;
use Oro\Bundle\MailChimpBundle\Model\StaticSegment\StaticSegmentsMemberStateManager;

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

        /** @var Channel[] $channelToSync */
        $channelToSync   = [];
        $channelSegments = [];
        foreach ($iterator as $staticSegment) {
            $channel = $staticSegment->getChannel();
            $channelToSync[$channel->getId()] = $channel;
            $channelSegments[$channel->getId()][] = $staticSegment->getId();
        }

        $output->writeln('Send export mail chimp message for channel:');
        foreach ($channelToSync as $channel) {
            $message = new Message();
            $message->setPriority(MessagePriority::VERY_LOW);
            $message->setBody([
                'integrationId' => $channel->getId(),
                'segmentsIds' => $channelSegments[$channel->getId()]
            ]);

            $output->writeln(sprintf(
                'Channel "%s" and segments "%s"',
                $message->getBody()['integrationId'],
                implode('", "', $message->getBody()['segmentsIds'])
            ));

            $this->getMessageProducer()->send(Topics::EXPORT_MAIL_CHIMP_SEGMENTS, $message);
        }

        $output->writeln('Completed');
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
