<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Reader;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
use OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator\MemberExportListIterator;

class MemberExportReader extends AbstractSubscribersListReader
{
    /**
     * @var string
     */
    protected $memberClassName;

    /**
     * @param string $memberClassName
     */
    public function setMemberClassName($memberClassName)
    {
        $this->memberClassName = $memberClassName;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        if (!$this->memberClassName) {
            throw new InvalidConfigurationException('Member class name must be provided');
        }

        if (!$this->getSourceIterator()) {
            $iterator = new MemberExportListIterator($this->getSubscribersListIterator(), $this->doctrineHelper);
            $iterator->setMemberClassName($this->memberClassName);

            $this->setSourceIterator($iterator);
        }
    }
}
