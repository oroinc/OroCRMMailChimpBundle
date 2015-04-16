<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\Strategy;

use Symfony\Component\Translation\Translator;

use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter;
use OroCRM\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use OroCRM\Bundle\MailChimpBundle\Entity\MemberExtendedMergeVar;
use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\Model\MarketingList\DataGridProviderInterface;

class MemberExtendedMergeVarStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * @var DataGridProviderInterface
     */
    protected $dataGridProvider;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var NumberFormatter
     */
    protected $numberFormatter;

    /**
     * @var DateTimeFormatter
     */
    protected $dateTimeFormatter;

    /**
     * @param DataGridProviderInterface $dataGridProvider
     */
    public function setDataGridProvider(DataGridProviderInterface $dataGridProvider)
    {
        $this->dataGridProvider = $dataGridProvider;
    }

    /**
     * @param Translator $translator
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param NumberFormatter $numberFormatter
     */
    public function setNumberFormatter($numberFormatter)
    {
        $this->numberFormatter = $numberFormatter;
    }

    /**
     * @param DateTimeFormatter $dateTimeFormatter
     */
    public function setDateTimeFormatter($dateTimeFormatter)
    {
        $this->dateTimeFormatter = $dateTimeFormatter;
    }

    /**
     * {@inheritdoc}
     */
    protected function afterProcessEntity($entity)
    {
        $this->prepareMmbrExtdMergeVarValues($entity);
        return parent::afterProcessEntity($entity);
    }

    /**
     * @param MemberExtendedMergeVar $entity
     * @return void
     * @throws \Exception
     */
    protected function prepareMmbrExtdMergeVarValues(MemberExtendedMergeVar $entity)
    {
        /** @var StaticSegment $staticSegment */
        $staticSegment = $this->databaseHelper->getEntityReference($entity->getStaticSegment());

        if (!$staticSegment) {
            return;
        }

        $extendedMergeVars = $staticSegment->getExtendedMergeVars();

        if (!$extendedMergeVars || $extendedMergeVars->isEmpty()) {
            return;
        }

        $itemData = $this->context->getValue('itemData');

        $columns = $this->dataGridProvider
            ->getDataGridColumns($staticSegment->getMarketingList());

        $mergeVarValues = array();
        foreach ($extendedMergeVars as $extendedMergeVar) {
            $value = $this->getValue($extendedMergeVar, $itemData, $columns);
            if ($value) {
                $mergeVarValues[$extendedMergeVar->getTag()] = $value;
            }
        }

        $entity->setMergeVarValues($mergeVarValues);
        $entity->setMergeVarValuesContext($itemData);
    }

    /**
     * @param ExtendedMergeVar $extendedMergeVar
     * @param array $itemData
     * @param array $columns
     * @return null|string
     */
    protected function getValue(ExtendedMergeVar $extendedMergeVar, array $itemData, array $columns)
    {
        $value = null;
        foreach ($columns as $columnName => $column) {
            $itemValueKey = $columnName . '_' . $extendedMergeVar->getName();
            if (isset($itemData[$itemValueKey])) {
                $value = $this->applyFrontendFormatting($itemData[$itemValueKey], $column);
                break;
            }
        }

        return $value;
    }

    /**
     * @param string $value
     * @param array $options
     * @return string
     */
    protected function applyFrontendFormatting($value, array $options)
    {
        $frontendType = isset($options['frontend_type']) ? $options['frontend_type'] : null;
        switch ($frontendType) {
            case PropertyInterface::TYPE_DATE:
                $value = $this->dateTimeFormatter->formatDate($value);
                break;
            case PropertyInterface::TYPE_DATETIME:
                $value = $this->dateTimeFormatter->format($value);
                break;
            case PropertyInterface::TYPE_DECIMAL:
                $value = $this->numberFormatter->formatDecimal($value);
                break;
            case PropertyInterface::TYPE_INTEGER:
                $value = $this->numberFormatter->formatDecimal($value);
                break;
            case PropertyInterface::TYPE_BOOLEAN:
                $value = $this->translator->trans((bool)$value ? 'Yes' : 'No', [], 'jsmessages');
                break;
            case PropertyInterface::TYPE_PERCENT:
                $value = $this->numberFormatter->formatPercent($value);
                break;
            case PropertyInterface::TYPE_CURRENCY:
                $value = $this->numberFormatter->formatCurrency($value);
                break;
            case PropertyInterface::TYPE_SELECT:
                if (isset($options['choices'][$value])) {
                    $value = $this->translator->trans($options['choices'][$value]);
                }
                break;
        }

        return $value;
    }
}
