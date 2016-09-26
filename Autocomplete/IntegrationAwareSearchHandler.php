<?php

namespace Oro\Bundle\MailChimpBundle\Autocomplete;

use Oro\Bundle\FormBundle\Autocomplete\SearchHandler;

abstract class IntegrationAwareSearchHandler extends SearchHandler
{
    /**
     * {@inheritdoc}
     */
    protected function checkAllDependenciesInjected()
    {
        if (!$this->entityRepository || !$this->idFieldName) {
            throw new \RuntimeException('Search handler is not fully configured');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function findById($query)
    {
        $parts = explode(';', $query);
        $id = $parts[0];
        $channelId = !empty($parts[1]) ? $parts[1] : false;

        $criteria = [$this->idFieldName => $id];
        if (false !== $channelId) {
            $criteria['channel'] = $channelId;
        }

        return [$this->entityRepository->findOneBy($criteria, null)];
    }
}
