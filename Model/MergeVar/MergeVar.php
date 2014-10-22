<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\MergeVar;

class MergeVar implements MergeVarInterface
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getDataValue(self::PROPERTY_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldType()
    {
        return $this->getDataValue(self::PROPERTY_FIELD_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getTag()
    {
        return $this->getDataValue(self::PROPERTY_TAG);
    }

    /**
     * {@inheritdoc}
     */
    public function isFirstName()
    {
        return $this->getTag() === self::TAG_FIRST_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function isLastName()
    {
        return $this->getTag() === self::TAG_LAST_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmail()
    {
        return $this->getTag() === self::TAG_EMAIL;
    }

    /**
     * {@inheritdoc}
     */
    public function isPhone()
    {
        return $this->getFieldType() === self::FIELD_TYPE_PHONE;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDataValue($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }
}
