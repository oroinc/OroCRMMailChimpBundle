<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Form\Type;

use Oro\Bundle\MailChimpBundle\Form\Type\StaticSegmentSyncModeType;
use Oro\Bundle\MailChimpBundle\Provider\StaticSegmentSyncModeChoicesProvider;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
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
     * @var StaticSegmentSyncModeChoicesProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    private $staticSegmentSyncModesProvider;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->staticSegmentSyncModesProvider = $this->createMock(StaticSegmentSyncModeChoicesProvider::class);

        $this->staticSegmentSyncModeType = new StaticSegmentSyncModeType($this->staticSegmentSyncModesProvider);
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        return [
            new PreloadedExtension(
                [
                    StaticSegmentSyncModeType::class => $this->staticSegmentSyncModeType
                ],
                []
            ),
        ];
    }

    public function testSubmitForm()
    {
        $this->mockStaticSegmentSyncModesProvider();

        $submittedData = 'scheduled';

        $form = $this->factory->create(StaticSegmentSyncModeType::class, null, []);

        $form->submit($submittedData);

        self::assertEquals($submittedData, $form->getData());
        self::assertEquals($submittedData, $form->getViewData());
        self::assertTrue($form->isSynchronized());
    }

    public function testConfigureOptions()
    {
        $this->mockStaticSegmentSyncModesProvider();

        $resolver = new OptionsResolver();
        $this->staticSegmentSyncModeType->configureOptions($resolver);

        $actualOptions = $resolver->resolve();
        $expectedOptions = [
            'required' => true,
            'choices' => self::CHOICES,
            'translatable_options' => false,
        ];

        self::assertEquals($expectedOptions, $actualOptions);
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
