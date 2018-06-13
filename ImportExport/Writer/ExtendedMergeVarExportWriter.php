<?php

namespace Oro\Bundle\MailChimpBundle\ImportExport\Writer;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter;
use Oro\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;
use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;
use Oro\Bundle\MailChimpBundle\Model\MergeVar\MergeVarInterface;
use Oro\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;
use Psr\Log\LoggerInterface;

class ExtendedMergeVarExportWriter extends AbstractExportWriter
{
    /** @var DQLNameFormatter */
    protected $nameFormatter;

    /** @var ContactInformationFieldsProvider */
    protected $contactInformationFieldsProvider;

    /**
     * @param DQLNameFormatter $nameFormatter
     */
    public function setNameFormatter(DQLNameFormatter $nameFormatter)
    {
        $this->nameFormatter = $nameFormatter;
    }

    /**
     * @param ContactInformationFieldsProvider $fieldsProvider
     */
    public function setFieldsProvider(ContactInformationFieldsProvider $fieldsProvider)
    {
        $this->contactInformationFieldsProvider = $fieldsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        /** @var ExtendedMergeVar $item */
        $item = $items[0];

        $transport = $item->getStaticSegment()->getChannel()->getTransport();
        $this->transport->init($transport);

        $items = new ArrayCollection($items);

        $itemsToWrite = [];

        try {
            $addedItems = $this->add($items);
            $removedItems = $this->remove($items);

            if ($addedItems) {
                $this->logger->info(sprintf('Extended merge vars: [%s] added', count($addedItems)));
            }

            if ($removedItems) {
                $this->logger->info(sprintf('Extended merge vars: [%s] removed', count($addedItems)));
            }

            $itemsToWrite = array_merge($itemsToWrite, $addedItems, $removedItems);
        } catch (\Exception $e) {
            $this->logger->error('Extended merge vars error occurs', ['exception' => $e]);
        }

        parent::write($itemsToWrite);
    }

    /**
     * @param ArrayCollection $items
     *
     * @return array
     */
    protected function add(ArrayCollection $items)
    {
        if ($items->isEmpty()) {
            return [];
        }

        /** @var StaticSegment $staticSegment */
        $staticSegment = $items->first()->getStaticSegment();

        // static segment fields <> MailChimp TAGs map
        $fieldsMap = $this->getMergeVarsFieldsMap($staticSegment);

        // merge vars from MailChimp
        $mergeVars = $this->getSubscribersListMergeVars($staticSegment->getSubscribersList());

        //merge vars calculated on marketing list basis
        $items = $items->filter(function (ExtendedMergeVar $extendedMergeVar) {
            return $extendedMergeVar->isAddState();
        });

        $successItems = [];
        /** @var ExtendedMergeVar $each */
        foreach ($items as $each) {
            if (isset($fieldsMap[$each->getName()])) {
                $each->setTag($fieldsMap[$each->getName()]);
            }
            $exists = array_filter($mergeVars, function ($var) use ($each) {
                return $var['tag'] === $each->getTag();
            });

            $response = [];
            if (empty($exists)) {
                $response = $this->transport->addListMergeVar(
                    [
                        'id'      => $each->getStaticSegment()->getSubscribersList()->getOriginId(),
                        'tag'     => $each->getTag(),
                        'name'    => $each->getLabel(),
                        'options' => [
                            'field_type' => $each->getFieldType(),
                            'require'    => $each->isRequired()
                        ]
                    ]
                );
            }

            $this->handleResponse(
                $response,
                function ($response, LoggerInterface $logger) use (&$successItems, $each) {
                    if (empty($response['errors'])) {
                        $each->markSynced();
                        $successItems[] = $each;
                    }

                    if (!empty($response['errors']) && is_array($response['errors'])) {
                        $logger->error(
                            'Mailchimp error occurs during execution "addListMergeVar" method',
                            [
                                'each_id'    => $each->getId(),
                                'each_label' => $each->getLabel(),
                            ]
                        );
                    }
                }
            );
        }

        return $successItems;
    }

    /**
     * @param ArrayCollection $items
     *
     * @return array
     */
    protected function remove(ArrayCollection $items)
    {
        $items = $items->filter(function (ExtendedMergeVar $extendedMergeVar) {
            return $extendedMergeVar->isRemoveState();
        });

        if ($items->isEmpty()) {
            return [];
        }
        $successItems = [];
        /** @var ExtendedMergeVar $each */
        foreach ($items as $each) {
            $each->markDropped();
            $successItems[] = $each;
        }

        return $successItems;
    }

    /**
     * Returns static segment fields mapped with mailChimp tags, e.g.
     * ['firstName' => 'FNAME', 'lastName' => 'LNAME', ...]
     *
     * @param StaticSegment $staticSegment
     *
     * @return array
     */
    private function getMergeVarsFieldsMap(StaticSegment $staticSegment)
    {
        $fieldsMap = [];
        $marketingList = $staticSegment->getMarketingList();
        $contactInformationFields = $this->contactInformationFieldsProvider
            ->getMarketingListTypedFields($marketingList);
        if ($contactInformationFields) {
            $contactInformationFields = array_flip($contactInformationFields);
            if (isset($contactInformationFields[ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL])) {
                $fieldsMap[$contactInformationFields[ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL]]
                    = MergeVarInterface::TAG_EMAIL;
            }
            if (isset($contactInformationFields[ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_PHONE])) {
                $fieldsMap[$contactInformationFields[ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_PHONE]]
                    = MergeVarInterface::TAG_PHONE;
            }
        }

        $nameFields = $this->nameFormatter->getSuggestedFieldNames($marketingList->getEntity());
        if ($nameFields) {
            if (isset($nameFields['first_name'])) {
                $fieldsMap[$nameFields['first_name']] = MergeVarInterface::TAG_FIRST_NAME;
            }
            if (isset($nameFields['last_name'])) {
                $fieldsMap[$nameFields['last_name']] = MergeVarInterface::TAG_LAST_NAME;
            }
        }

        return $fieldsMap;
    }
}
