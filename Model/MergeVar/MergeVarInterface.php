<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\MergeVar;

/**
 * @link http://apidocs.mailchimp.com/api/2.0/lists/merge-vars.php
 */
interface MergeVarInterface
{
    /**#@+
     * @const string Field type of MergeVar
     */
    const FIELD_TYPE_EMAIL = 'email';
    const FIELD_TYPE_PHONE = 'phone';
    /**#@-*/

    /**#@+
     * @const string Tags of MergeVar
     */
    const TAG_EMAIL = 'EMAIL';
    const TAG_FIRST_NAME = 'FNAME';
    const TAG_LAST_NAME = 'LNAME';
    /**#@-*/

    /**#@+
     * @const string Name of properties of MergeVar
     */
    const PROPERTY_NAME = 'name';
    const PROPERTY_REQUIRED = 'req';
    const PROPERTY_FIELD_TYPE = 'field_type';
    const PROPERTY_TAG = 'tag';
    /**#@-*/

    /**
     * @return bool
     */
    public function isFirstName();

    /**
     * @return bool
     */
    public function isLastName();

    /**
     * @return bool
     */
    public function isEmail();

    /**
     * @return bool
     */
    public function isPhone();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getFieldType();

    /**
     * @return string
     */
    public function getTag();
}
