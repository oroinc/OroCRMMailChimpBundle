<?php

namespace Oro\Bundle\MailChimpBundle\Provider;

use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;
use Oro\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use Oro\Bundle\MailChimpBundle\Model\ExtendedMergeVar\ProviderInterface;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Component\PhpUtils\ArrayUtil;

class MarketingListExtendedMergeVarProvider implements ProviderInterface
{
    /** @var EntityFieldProvider */
    protected $entityFieldProvider;

    /** @var array */
    protected $fieldTypeToMergeVarType = [
        'date' => ExtendedMergeVar::TAG_DATE_FIELD_TYPE,
        'integer' => ExtendedMergeVar::TAG_NUMBER_FIELD_TYPE,
        'float' => ExtendedMergeVar::TAG_NUMBER_FIELD_TYPE,
        'bigint' => ExtendedMergeVar::TAG_NUMBER_FIELD_TYPE,
    ];

    /**
     * @param EntityFieldProvider $entityFieldProvider
     */
    public function __construct(EntityFieldProvider $entityFieldProvider)
    {
        $this->entityFieldProvider = $entityFieldProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(MarketingList $marketingList)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function provideExtendedMergeVars(MarketingList $marketingList)
    {
        $definition = json_decode($marketingList->getDefinition(), true);

        $fields = $this->entityFieldProvider->getFields(
            $marketingList->getEntity(),
            true,
            true,
            false,
            true,
            true,
            true
        );

        return $this->convertColumnsDefinitionToExtendMergeVars($definition['columns'], $fields);
    }

    /**
     * @param array $columnsDefinition
     * @param array $fields
     *
     * @return array
     */
    protected function convertColumnsDefinitionToExtendMergeVars(array $columnsDefinition, array $fields)
    {
        return array_map(
            function ($column) use ($fields) {
                $var = array_intersect_key($column, ['name' => null, 'label' => null]);

                $fieldType = $this->getFieldType($var['name'], $fields);
                if ($fieldType) {
                    $var['fieldType'] = $fieldType;
                }

                return $var;
            },
            $columnsDefinition
        );
    }

    /**
     * @param string $fieldName
     * @param array $fields
     *
     * @return string|null
     */
    protected function getFieldType($fieldName, array $fields)
    {
        $field = ArrayUtil::find(
            function (array $field) use ($fieldName) {
                return $field['name'] === $fieldName;
            },
            $fields
        );

        if ($field && isset($this->fieldTypeToMergeVarType[$field['type']])) {
            return $this->fieldTypeToMergeVarType[$field['type']];
        }

        return null;
    }
}
