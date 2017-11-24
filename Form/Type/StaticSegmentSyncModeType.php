<?php

namespace Oro\Bundle\MailChimpBundle\Form\Type;

use Oro\Bundle\MailChimpBundle\Provider\StaticSegmentSyncModeChoicesProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StaticSegmentSyncModeType extends AbstractType
{
    const NAME = 'oro_mailchimp_static_segment_sync_mode';

    /**
     * @var StaticSegmentSyncModeChoicesProvider
     */
    private $staticSegmentSyncModesProvider;

    /**
     * @param StaticSegmentSyncModeChoicesProvider $staticSegmentSyncModesProvider
     */
    public function __construct(StaticSegmentSyncModeChoicesProvider $staticSegmentSyncModesProvider)
    {
        $this->staticSegmentSyncModesProvider = $staticSegmentSyncModesProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'required' => true,
                'choices' => $this->staticSegmentSyncModesProvider->getTranslatedChoices(),
                // We expect that staticSegmentSyncModesProvider returns already translated choices list, so we disable
                // the translation in template.
                'translatable_options' => false,
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
