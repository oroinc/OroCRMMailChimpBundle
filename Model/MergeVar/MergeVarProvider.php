<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\MergeVar;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;

class MergeVarProvider implements MergeVarProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getMergeVarFields(SubscribersList $subscribersList)
    {
        $result = $subscribersList->getMergeVarFields();

        if (!$result) {
            $result = $this->createMergeVarFields($subscribersList->getMergeVarConfig());
            $subscribersList->setMergeVarFields($result);
        }

        return $result;
    }

    /**
     * @param array $config
     * @return MergeVarFieldsInterface
     */
    protected function createMergeVarFields(array $config)
    {
        $mergeVars = [];
        foreach ($config as $data) {
            $mergeVars[] = $this->createMergeVar($data);
        }

        return new MergeVarFields($mergeVars);
    }

    /**
     * @param array $data
     * @return MergeVarInterface
     */
    protected function createMergeVar(array $data)
    {
        return new MergeVar($data);
    }

    /**
     * {@inheritdoc}
     */
    public function assignMergeVarValues(Member $member, MergeVarFieldsInterface $fields)
    {
        $values = $member->getMergeVarValues();

        $member->setEmail($this->getMergeVarValue($values, $fields->getEmail()));
        $member->setPhone($this->getMergeVarValue($values, $fields->getPhone()));
        $member->setFirstName($this->getMergeVarValue($values, $fields->getFirstName()));
        $member->setLastName($this->getMergeVarValue($values, $fields->getLastName()));
    }

    /**
     * @param array $values
     * @param MergeVarInterface $field
     * @return null
     */
    protected function getMergeVarValue(array $values, MergeVarInterface $field = null)
    {
        if ($field && $field->getName() && isset($values[$field->getName()])) {
            return $values[$field->getName()];
        }

        return null;
    }
}
