<?php

namespace OroCRM\Bundle\MailChimpBundle\Provider\Transport\Iterator;

use OroCRM\Bundle\MailChimpBundle\Entity\StaticSegment;
use OroCRM\Bundle\MailChimpBundle\ImportExport\Writer\AbstractInsertFromSelectWriter;
use OroCRM\Bundle\MailChimpBundle\Model\FieldHelper;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;

class MarketingListEmailIterator extends AbstractStaticSegmentIterator
{
    /**
     * @var ContactInformationFieldsProvider
     */
    protected $contactInformationFieldsProvider;

    /**
     * @var FieldHelper
     */
    protected $fieldHelper;

    /**
     * @param ContactInformationFieldsProvider $provider
     * @return MarketingListEmailIterator
     */
    public function setContactInformationFieldsProvider(ContactInformationFieldsProvider $provider)
    {
        $this->contactInformationFieldsProvider = $provider;

        return $this;
    }

    /**
     * @param FieldHelper $fieldHelper
     * @return MarketingListEmailIterator
     */
    public function setFieldHelper($fieldHelper)
    {
        $this->fieldHelper = $fieldHelper;

        return $this;
    }

    /**
     * @param StaticSegment $staticSegment
     *
     * {@inheritdoc}
     */
    protected function createSubordinateIterator($staticSegment)
    {
        $marketingList = $staticSegment->getMarketingList();
        $qb = $this->getIteratorQueryBuilder($staticSegment);

        $contactInformationFields = $this->contactInformationFieldsProvider->getMarketingListTypedFields(
            $marketingList,
            ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
        );
        $emailField = reset($contactInformationFields);
        $contactInformationFieldExpr = $this->fieldHelper
            ->getFieldExpr($marketingList->getEntity(), $qb, $emailField);

        $qb
            ->select([
                $marketingList->getId() . ' AS marketingListId',
                $contactInformationFieldExpr . ' AS email'
            ])
            ->resetDQLPart('orderBy')
            ->groupBy($contactInformationFieldExpr);

        return new \ArrayIterator(
            [
                [
                    AbstractInsertFromSelectWriter::QUERY_BUILDER => $qb,
                    'marketing_list_id' => $marketingList->getId()
                ]
            ]
        );
    }
}
