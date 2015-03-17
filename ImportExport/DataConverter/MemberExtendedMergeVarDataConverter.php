<?php

namespace OroCRM\Bundle\MailChimpBundle\ImportExport\DataConverter;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\ImportExportBundle\Converter\DataConverterInterface;
use OroCRM\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;

class MemberExtendedMergeVarDataConverter implements DataConverterInterface
{
    /**
     * @var DataConverterInterface
     */
    private $memberCartMergeVarDataConverter;

    /**
     * @param DataConverterInterface $memberCartMergeVarDataConverter
     */
    public function __construct(DataConverterInterface $memberCartMergeVarDataConverter)
    {
        $this->memberCartMergeVarDataConverter = $memberCartMergeVarDataConverter;
    }

    /**
     * @inheritdoc
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        if (false === isset($importedRecord['extended_merge_vars'])) {
            return array();
        }
        $extendedMergeVars = $importedRecord['extended_merge_vars'];
        if (false === ($extendedMergeVars instanceof ArrayCollection)) {
            return array();
        }
        $result = array();
        /** @var ExtendedMergeVar $each */
        foreach ($extendedMergeVars as $each) {
            if (false === ($each instanceof ExtendedMergeVar)) {
                throw new \RuntimeException(
                    'Each element in extended_merge_vars array should be ExtendedMergeVar object.'
                );
            }
            if (isset($importedRecord[$each->getNameWithPrefix()])) {
                $result[$each->getTag()] = $importedRecord[$each->getNameWithPrefix()];
            }
        }

        $cartMergeVars = $this->memberCartMergeVarDataConverter
            ->convertToImportFormat($importedRecord, $skipNullValues);

        $result = array_merge($result, $cartMergeVars);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function convertToExportFormat(array $exportedRecord, $skipNullValues = true)
    {
        throw new \Exception('Is not implemented.');
    }
}
