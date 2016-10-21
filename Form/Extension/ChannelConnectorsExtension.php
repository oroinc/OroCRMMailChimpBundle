<?php

namespace Oro\Bundle\MailChimpBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MailChimpBundle\Provider\ChannelType;

class ChannelConnectorsExtension extends AbstractTypeExtension
{
    const CLASS_PATH = '[attr][class]';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            [$this, 'onPostSetData']
        );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            [$this, 'onPostSubmit']
        );
    }

    /**
     * @param Channel $data
     * @return bool
     */
    public function isApplicable(Channel $data = null)
    {
        return $data && $data->getType() === ChannelType::TYPE;
    }

    /**
     * Hide connectors for MailChimp channel
     *
     * @param FormEvent $event
     */
    public function onPostSetData(FormEvent $event)
    {
        $data = $event->getData();
        if (!$this->isApplicable($data)) {
            return;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $options          = $event->getForm()['connectors']->getConfig()->getOptions();
        $class            = $propertyAccessor->getValue($options, self::CLASS_PATH);

        FormUtils::replaceField(
            $event->getForm(),
            'connectors',
            [
                'attr' => [
                    'class' => implode(' ', [$class, 'hide'])
                ]
            ]
        );
    }

    /**
     * Set all connectors to MailChimp channel
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (!$this->isApplicable($data)) {
            return;
        }
        $options = $event->getForm()['connectors']->getConfig()->getOptions();
        $connectors = array_values($options['choices']);
        $data->setConnectors($connectors);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'oro_integration_channel_form';
    }
}
