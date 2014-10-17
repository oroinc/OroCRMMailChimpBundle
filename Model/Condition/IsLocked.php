<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Exception\ConditionException;
use Oro\Bundle\WorkflowBundle\Model\Condition\AbstractCondition;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

use OroCRM\Bundle\MailChimpBundle\Model\Lock\Lock;

/**
 * TODO Move this class to OroWorkflowBundle or remove after CRM-1635
 */
class IsLocked extends AbstractCondition
{
    /**
     * @var ContextAccessor
     */
    protected $contextAccessor;

    /**
     * @var mixed
     */
    protected $name;

    /**
     * Constructor
     *
     * @param ContextAccessor $contextAccessor
     * @param Lock $lock
     */
    public function __construct(ContextAccessor $contextAccessor, Lock $lock)
    {
        $this->contextAccessor = $contextAccessor;
        $this->lock = $lock;
    }

    /**
     * Returns TRUE is target is empty in context
     *
     * @param mixed $context
     * @return boolean
     */
    protected function isConditionAllowed($context)
    {
        $value = $this->contextAccessor->getValue($context, $this->name);
        return $this->lock->isLocked($value);
    }

    /**
     * Initialize target that will be checked for emptiness
     *
     * @param array $options
     * @return Blank
     * @throws ConditionException
     */
    public function initialize(array $options)
    {
        if (1 == count($options)) {
            $this->name = reset($options);
        } else {
            throw new ConditionException(
                sprintf(
                    'Options must have 1 element, but %d given',
                    count($options)
                )
            );
        }
    }
}
