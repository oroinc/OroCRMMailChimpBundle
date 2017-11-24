<?php

namespace Oro\Bundle\MailChimpBundle\Provider;

use Oro\Bundle\CronBundle\Entity\Schedule;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Symfony\Component\Translation\TranslatorInterface;

class StaticSegmentSyncModeChoicesProvider implements StaticSegmentSyncModeChoicesProviderInterface
{
    /**
     * @internal
     */
    const CRON_COMMAND_NAME = 'oro:cron:mailchimp:export';

    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param DoctrineHelper      $doctrineHelper
     * @param TranslatorInterface $translator
     */
    public function __construct(DoctrineHelper $doctrineHelper, TranslatorInterface $translator)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public function getTranslatedChoices(): array
    {
        return [
            'on_update' => $this->translator
                ->trans('oro.mailchimp.configuration.fields.static_segment_sync_mode.choices.on_update'),
            'scheduled' => $this->translator->trans(
                'oro.mailchimp.configuration.fields.static_segment_sync_mode.choices.scheduled',
                ['{{ schedule_definition }}' => $this->getCronScheduleDefinition(self::CRON_COMMAND_NAME)]
            ),
        ];
    }

    /**
     * @param string $commandName
     *
     * @return string
     */
    private function getCronScheduleDefinition(string $commandName): string
    {
        $scheduleRepository = $this->doctrineHelper->getEntityRepository(Schedule::class);

        /** @var Schedule $schedule */
        $schedule = $scheduleRepository->findOneBy(['command' => $commandName]);
        if (!$schedule) {
            return '';
        }

        return $schedule->getDefinition();
    }
}
