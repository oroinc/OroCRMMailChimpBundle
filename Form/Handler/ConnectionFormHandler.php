<?php

namespace OroCRM\Bundle\MailChimpBundle\Form\Handler;

use Oro\Bundle\FormBundle\Form\Handler\ApiFormHandler;

use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;

class ConnectionFormHandler extends ApiFormHandler
{
    /** @var StaticSegment */
    protected $oldSegment;

    /**
     * @param StaticSegment $entity
     * @return bool
     */
    public function process($entity)
    {
        if ($entity->getId()) {
            $this->oldSegment = $entity;
            $entity = $entity->createNewCopy();
        }

        return parent::process($entity);
    }

    /**
     * @param StaticSegment $entity
     */
    protected function onSuccess($entity)
    {
        if ($this->oldSegment) {
            $this->manager->remove($this->oldSegment);
        }

        parent::onSuccess($entity);
    }
}
