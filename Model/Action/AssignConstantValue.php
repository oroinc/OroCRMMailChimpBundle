<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\Action;

use Oro\Bundle\WorkflowBundle\Model\Action\AbstractAction;
use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;

/**
 * TODO Move this class to OroWorkflowBundle
 */
class AssignConstantValue extends AbstractAction
{
    const NAME = 'assign_constant_value';

    /**
     * @var mixed
     */
    protected $attribute;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * {@inheritdoc}
     */
    protected function executeAction($context)
    {
        $value = $this->contextAccessor->getValue($context, $this->value);
        if (!is_string($value) || (is_object($value) && !method_exists('__toString', $value))) {
            throw new InvalidParameterException(
                sprintf(
                    'Action "%s" expects a string in parameter "name", %s is given.',
                    self::NAME,
                    is_object($value) ? get_class($value) : gettype($value)
                )
            );
        }


        $this->contextAccessor->setValue(
            $context,
            $this->attribute,
            $this->evaluateConstant((string)$value)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (count($options) != 2) {
            throw new InvalidParameterException('Attribute and value parameters are required.');
        }

        if (isset($options['attribute'])) {
            $this->attribute = $options['attribute'];
        } elseif (isset($options[0])) {
            $this->attribute = $options[0];
        } else {
            throw new InvalidParameterException('Attribute must be defined.');
        }

        if (isset($options['value'])) {
            $this->value = $options['value'];
        } elseif (isset($options[1])) {
            $this->value = $options[1];
        } else {
            throw new InvalidParameterException('Attribute must be defined.');
        }

        return $this;
    }

    /**
     * @param string $value
     * @return mixed
     * @throws InvalidParameterException
     */
    protected function evaluateConstant($value)
    {
        if (defined($value)) {
            return constant($value);
        }

        $parts = explode('::', $value);

        if (count($parts) == 2 && !class_exists($parts[0])) {
            throw new InvalidParameterException("Cannot evaluate value of \"$value\", class is not exist.");
        }

        throw new InvalidParameterException("Cannot evaluate value of \"$value\", constant is not exist.");
    }
}
