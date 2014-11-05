<?php

namespace OroCRM\Bundle\MailChimpBundle\Form\Handler;

use Oro\Bundle\FormBundle\Form\Handler\ApiFormHandler;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;

class ConnectionFormHandler extends ApiFormHandler
{
    /**
     * @var SubscribersList
     */
    protected $oldSubscribersList;

    /**
     * @param StaticSegment $entity
     * @return bool
     */
    public function process($entity)
    {
        $this->oldSubscribersList = $entity->getSubscribersList();

        return parent::process($entity);
    }

    /**
     * @param StaticSegment $entity
     */
    protected function onSuccess($entity)
    {
        // Reset originId of static segment if subscribers list was changed to force list creation
        if ($this->oldSubscribersList
            && $this->oldSubscribersList->getId() != $entity->getSubscribersList()->getId()
        ) {
            $entity->setOriginId(null);
        }

        parent::onSuccess($entity);
    }
}
