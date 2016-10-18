<?php

namespace Oro\Bundle\MailChimpBundle\Model\MergeVar;

interface MergeVarFieldsInterface
{
    /**
     * Get email field.
     *
     * @return MergeVarInterface|null
     */
    public function getEmail();

    /**
     * Get first name field.
     *
     * @return MergeVarInterface|null
     */
    public function getFirstName();

    /**
     * Get last name field.
     *
     * @return MergeVarInterface|null
     */
    public function getLastName();

    /**
     * Get phone field.
     *
     * @return MergeVarInterface|null
     */
    public function getPhone();
}
