<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Form\Type;

use Oro\Bundle\MailChimpBundle\Form\Type\StaticSegmentSyncModeType;
use Oro\Bundle\MailChimpBundle\Provider\StaticSegmentSyncModeChoicesProviderInterface;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StaticSegmentSyncModeTypeTest extends FormIntegrationTestCase
{
    /**
     * @internal
     */
    const CHOICES = ['scheduled' => 'scheduled', 'on_update' => 'on_update'];

    /**
     * @var StaticSegmentSyncModeType
     */
    private $staticSegmentSyncModeType;

    /**
     * @var StaticSegmentSyncModeChoicesProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $staticSegmentSyncModesProvider;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->staticSegmentSyncModesProvider = $this->createMock(StaticSegmentSyncModeChoicesProviderInterface::class);

        $this->staticSegmentSyncModeType = new StaticSegmentSyncModeType($this->staticSegmentSyncModesProvider);
    }

    public function testSubmitForm()
    {
        $this->mockStaticSegmentSyncModesProvider();

        $submittedData = 'scheduled';

        $form = $this->factory->create($this->staticSegmentSyncModeType, null, []);

        $form->submit($submittedData);

        self::assertEquals($submittedData, $form->getData());
        self::assertEquals($submittedData, $form->getViewData());
        self::assertTrue($form->isSynchronized());
    }

    public function testConfigureOptions()
    {
        $this->mockStaticSegmentSyncModesProvider();

        $resolver = $this->createMock(OptionsResolver::class);
        $resolver
            ->expects(self::once())
            ->method('setDefaults')
            ->with([
                'required' => true,
                'choices' => self::CHOICES,
                'translatable_options' => false,
            ]);

        $this->staticSegmentSyncModeType
            ->configureOptions($resolver);
    }

    public function testGetBlockPrefix()
    {
        self::assertEquals(StaticSegmentSyncModeType::NAME, $this->staticSegmentSyncModeType->getBlockPrefix());
    }

    public function testGetParent()
    {
        self::assertEquals(ChoiceType::class, $this->staticSegmentSyncModeType->getParent());
    }

    private function mockStaticSegmentSyncModesProvider()
    {
        $this->staticSegmentSyncModesProvider
            ->expects(self::once())
            ->method('getTranslatedChoices')
            ->willReturn(self::CHOICES);
    }
}
