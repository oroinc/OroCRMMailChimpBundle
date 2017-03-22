UPGRADE FROM 2.0 to 2.1
========================

####General
- Changed minimum required php version to 7.0
- Updated dependency to [fxpio/composer-asset-plugin](https://github.com/fxpio/composer-asset-plugin) composer plugin to version 1.3.
- Composer updated to version 1.4.

```
    composer self-update
    composer global require "fxp/composer-asset-plugin"
```

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
