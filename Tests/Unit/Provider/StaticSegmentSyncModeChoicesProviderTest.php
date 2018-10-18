<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Provider;

use Oro\Bundle\CronBundle\Entity\Repository\ScheduleRepository;
use Oro\Bundle\CronBundle\Entity\Schedule;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MailChimpBundle\Provider\StaticSegmentSyncModeChoicesProvider;
use Symfony\Component\Translation\TranslatorInterface;

class StaticSegmentSyncModeChoicesProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StaticSegmentSyncModeChoicesProvider
     */
    private $staticSegmentSyncModeChoicesProvider;

    /**
     * @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $doctrineHelper;

    /**
     * @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $translator;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);

        $this->staticSegmentSyncModeChoicesProvider = new StaticSegmentSyncModeChoicesProvider(
            $this->doctrineHelper,
            $this->translator
        );
    }

    /**
     * @dataProvider  getChoicesDataProvider
     *
     * @param Schedule|null $schedule
     * @param string $scheduleDefinition
     */
    public function testGetChoices(Schedule $schedule = null, string $scheduleDefinition)
    {
        $this->translator
            ->expects(self::exactly(2))
            ->method('trans')
            ->withConsecutive(
                ['oro.mailchimp.configuration.fields.static_segment_sync_mode.choices.on_update'],
                [
                    'oro.mailchimp.configuration.fields.static_segment_sync_mode.choices.scheduled',
                    ['{{ schedule_definition }}' => $scheduleDefinition]
                ]
            )
            ->willReturnArgument(0);

        $scheduleRepository = $this->createMock(ScheduleRepository::class);
        $this->doctrineHelper
            ->expects(self::once())
            ->method('getEntityRepository')
            ->with(Schedule::class)
            ->willReturn($scheduleRepository);
        $scheduleRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['command' => 'oro:cron:mailchimp:export'])
            ->willReturn($schedule);

        $choices = $this->staticSegmentSyncModeChoicesProvider->getTranslatedChoices();

        $expectedChoices = [
            'on_update' => 'oro.mailchimp.configuration.fields.static_segment_sync_mode.choices.on_update',
            'scheduled' => 'oro.mailchimp.configuration.fields.static_segment_sync_mode.choices.scheduled',
        ];

        self::assertSame($expectedChoices, $choices);
    }

    /**
     * @return array
     */
    public function getChoicesDataProvider()
    {
        $definition = '*/5 * * * *';

        return [
            'schedule exists' => [
                'schedule' => (new Schedule())->setDefinition($definition),
                'scheduleDefinition' => $definition,
            ],
            'schedule does not exist' => [
                'schedule' => null,
                'scheduleDefinition' => '',
            ],
        ];
    }
}
