<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

abstract class AbstractMailChimpConnector extends AbstractConnector
{
    /**
     * @var MailChimpTransport
     */
    protected $transport;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @param string $entityName
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }
}
