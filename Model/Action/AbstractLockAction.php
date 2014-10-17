<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\Action;

use Oro\Bundle\WorkflowBundle\Model\Action\AbstractAction;
use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

use OroCRM\Bundle\MailChimpBundle\Model\Lock\Lock as LockState;

/**
 * TODO Move this class to OroWorkflowBundle or remove after CRM-1635
 */
abstract class AbstractLockAction extends AbstractAction
{
    /**
     * @var mixed
     */
    protected $name;

    /**
     * @param ContextAccessor $contextAccessor
     * @param LockState $lock
     */
    public function __construct(ContextAccessor $contextAccessor, LockState $lock)
    {
        parent::__construct($contextAccessor);

        $this->lock = $lock;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (1 == count($options)) {
            $this->name = reset($options);
        } else {
            throw new InvalidParameterException(
                sprintf(
                    'Action "%s" must have 1 parameter, but %d given',
                    $this->getActionName(),
                    count($options)
                )
            );
        }

        return $this;
    }

    /**
     * @param mixed $context
     * @return string
     * @throws InvalidParameterException
     */
    protected function getLockName($context)
    {
        $name = $this->contextAccessor->getValue($context, $this->name);
        if (!is_string($name) || (is_object($name) && !method_exists($name, '__toString'))) {
            throw new InvalidParameterException(
                sprintf(
                    'Action "%s" expects a string in parameter "name", %s is given.',
                    $this->getActionName(),
                    is_object($name) ? get_class($name) : gettype($name)
                )
            );
        }
        return (string)$name;
    }

    /**
     * @return string
     */
    abstract protected function getActionName();
}
