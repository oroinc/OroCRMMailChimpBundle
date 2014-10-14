<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\MergeVar;

class MergeVarFields implements MergeVarFieldsInterface
{
    /**
     * @var MergeVarInterface[]
     */
    protected $mergeVars;

    /**
     * @param array $mergeVars
     */
    public function __construct(array $mergeVars)
    {
        $this->mergeVars = $mergeVars;
    }

    /**
     * Get email field.
     *
     * @return MergeVarInterface|null
     */
    public function getEmail()
    {
        $fields = $this->filterFields(
            function (MergeVarInterface $mergeVar) {
                return $mergeVar->isEmail();
            }
        );

        return $fields ? current($fields) : null;
    }

    /**
     * Get first name field.
     *
     * @return MergeVarInterface|null
     */
    public function getFirstName()
    {
        $fields = $this->filterFields(
            function (MergeVarInterface $mergeVar) {
                return $mergeVar->isFirstName();
            }
        );

        return $fields ? current($fields) : null;
    }

    /**
     * Get phone field.
     *
     * @return MergeVarInterface|null
     */
    public function getPhone()
    {
        $fields = $this->filterFields(
            function (MergeVarInterface $mergeVar) {
                return $mergeVar->isPhone();
            }
        );

        return $fields ? current($fields) : null;
    }

    /**
     * Get last name field.
     *
     * @return MergeVarInterface|null
     */
    public function getLastName()
    {
        $fields = $this->filterFields(
            function (MergeVarInterface $mergeVar) {
                return $mergeVar->isLastName();
            }
        );

        return $fields ? current($fields) : null;
    }

    /**
     * @param callable $callback
     * @return MergeVarInterface[]
     */
    public function filterFields($callback)
    {
        $result = [];

        /** @var MergeVar $mergeVar */
        foreach ($this->mergeVars as $mergeVar) {
            if (call_user_func($callback, $mergeVar)) {
                $result[] = $mergeVar;
            }
        }

        return $result;
    }
}
