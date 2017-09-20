## 2.5.0 (Unreleased)
## 2.4.0 (Unreleased)

## 2.3.1 (2017-08-22)
## 2.3.0 (2017-07-28)
[Show detailed list of changes](file-incompatibilities-2-3-0.md)

## 2.2.5 (2017-08-30)
## 2.2.4 (2017-08-18)
## 2.2.3 (2017-08-16)
## 2.2.2 (2017-07-19)
## 2.2.1 (2017-06-29)
## 2.2.0 (2017-05-31)
[Show detailed list of changes](file-incompatibilities-2-2-0.md)

## 2.1.2 (2017-06-29)
## 2.1.1 (2017-06-16)
## 2.1.0 (2017-03-30)
### Changed
- Class `Oro\Bundle\MailChimpBundle\Entity\Repository\StaticSegmentRepository`
    - changed the return type of `getStaticSegments` method from `BufferedQueryResultIterator` to `BufferedQueryResultIteratorInterface`
- Class `Oro\Bundle\MailChimpBundle\ImportExport\Reader\AbstractExtendedMergeVarExportReader`
    - changed the return type of `getSegmentsIterator` method from `BufferedQueryResultIterator` to `BufferedQueryResultIteratorInterface`
- Class `Oro\Bundle\MailChimpBundle\ImportExport\Reader\MemberExportReader`
    - changed the return type of `getSubscribersListIterator` method from `BufferedQueryResultIterator` to `\Iterator`
- Class `Oro\Bundle\MailChimpBundle\ImportExport\Reader\StaticSegmentReader`
    - changed the return type of `getStaticSegmentIterator` method from `BufferedQueryResultIterator` to `\Iterator`
- Class `Oro\Bundle\MailChimpBundle\ImportExport\Writer\StaticSegmentExportWriter`
    - changed the return type of `getSegmentMembersEmailsIterator` method from `BufferedQueryResultIterator` to `BufferedQueryResultIteratorInterface`
- Class `Oro\Bundle\MailChimpBundle\Model\Action\AbstractMarketingListEntitiesAction`
    - changed the return type of `getMarketingListEntitiesByEmail` method from `BufferedQueryResultIterator` to `\Iterator`
- Class `Oro\Bundle\MailChimpBundle\Model\Action\MarketingListStateItemAction`
    - changed the return type of `getMarketingListIterator` method from `BufferedQueryResultIterator` to `\Iterator`
## 2.0.9 (2017-08-30)
## 2.0.8 (2017-08-21)
## 2.0.7 (2017-08-16)
## 2.0.6 (2017-07-19)
## 2.0.5 (2017-06-29)
## 2.0.4 (2017-06-19)
## 2.0.3 (2017-04-05)
## 2.0.2 (2017-03-17)
## 2.0.1 (2017-02-06)
## 2.0.0 (2017-01-16)
