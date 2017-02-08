UPGRADE FROM 2.0 to 2.1
========================

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
