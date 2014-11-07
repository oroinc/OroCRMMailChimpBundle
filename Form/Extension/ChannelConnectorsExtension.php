<?php

namespace OroCRM\Bundle\MailChimpBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\MailChimpBundle\Provider\ChannelType;

class ChannelConnectorsExtension extends AbstractTypeExtension
{
    const CLASS_PATH = '[attr][class]';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $field = $builder->get('connectors');
        $options = $field->getOptions();

        $class = $propertyAccessor->getValue($options, self::CLASS_PATH);
        $propertyAccessor->setValue($options, self::CLASS_PATH, implode('', [$class, 'hide']));

        $builder->add('connectors', $field->getType()->getName(), $options);

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            [$this, 'onPostSubmit']
        );
    }

    /**
     * @param Channel $data
     * @return bool
     */
    public function isApplicable(Channel $data)
    {
        return $data && $data->getType() === ChannelType::TYPE;
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
        $connectors = array_keys($options['choices']);
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
