Please refer first to [UPGRADE.md](UPGRADE.md) for the most important items that should be addressed before attempting to upgrade or during the upgrade of a vanilla Oro application.

The current file describes significant changes in the code that may affect the upgrade of your customizations.

## 3.1.4
### Changed
* In `Oro\Bundle\MailChimpBundle\Controller\MailChimpController::toggleUpdateStateAction` 
 (`oro_mailchimp_email_campaign_activity_update_toggle` route)
 action the request method was changed to POST. 
 
## 3.1.0-rc (2018-11-30)
[Show detailed list of changes](incompatibilities-3-1-rc.md)

## 3.0.0-rc (2018-05-31)
[Show detailed list of changes](incompatibilities-3-0-rc.md)

## 3.0.0-beta (2018-03-30)
[Show detailed list of changes](incompatibilities-3-0-beta.md)

## 2.3.0 (2017-07-28)
[Show detailed list of changes](incompatibilities-2-3.md)

## 2.2.0 (2017-05-31)
[Show detailed list of changes](incompatibilities-2-2.md)

## 2.1.0 (2017-03-30)
### Changed
- Class `StaticSegmentRepository`<sup>[[?]](https://github.com/oroinc/OroCRMMailChimpBundle/tree/2.1.0/Entity/Repository/StaticSegmentRepository.php "Oro\Bundle\MailChimpBundle\Entity\Repository\StaticSegmentRepository")</sup>
    - changed the return type of `getStaticSegments` method from `BufferedQueryResultIterator` to `BufferedQueryResultIteratorInterface`
- Class `AbstractExtendedMergeVarExportReader`<sup>[[?]](https://github.com/oroinc/OroCRMMailChimpBundle/tree/2.1.0/ImportExport/Reader/AbstractExtendedMergeVarExportReader.php "Oro\Bundle\MailChimpBundle\ImportExport\Reader\AbstractExtendedMergeVarExportReader")</sup>
    - changed the return type of `getSegmentsIterator` method from `BufferedQueryResultIterator` to `BufferedQueryResultIteratorInterface`
- Class `MemberExportReader`<sup>[[?]](https://github.com/oroinc/OroCRMMailChimpBundle/tree/2.1.0/ImportExport/Reader/MemberExportReader.php "Oro\Bundle\MailChimpBundle\ImportExport\Reader\MemberExportReader")</sup>
    - changed the return type of `getSubscribersListIterator` method from `BufferedQueryResultIterator` to `\Iterator`
- Class `StaticSegmentReader`<sup>[[?]](https://github.com/oroinc/OroCRMMailChimpBundle/tree/2.1.0/ImportExport/Reader/StaticSegmentReader.php "Oro\Bundle\MailChimpBundle\ImportExport\Reader\StaticSegmentReader")</sup>
    - changed the return type of `getStaticSegmentIterator` method from `BufferedQueryResultIterator` to `\Iterator`
- Class `StaticSegmentExportWriter`<sup>[[?]](https://github.com/oroinc/OroCRMMailChimpBundle/tree/2.1.0/ImportExport/Writer/StaticSegmentExportWriter.php "Oro\Bundle\MailChimpBundle\ImportExport\Writer\StaticSegmentExportWriter")</sup>
    - changed the return type of `getSegmentMembersEmailsIterator` method from `BufferedQueryResultIterator` to `BufferedQueryResultIteratorInterface`
- Class `AbstractMarketingListEntitiesAction`<sup>[[?]](https://github.com/oroinc/OroCRMMailChimpBundle/tree/2.1.0/Model/Action/AbstractMarketingListEntitiesAction.php "Oro\Bundle\MailChimpBundle\Model\Action\AbstractMarketingListEntitiesAction")</sup>
    - changed the return type of `getMarketingListEntitiesByEmail` method from `BufferedQueryResultIterator` to `\Iterator`
- Class `MarketingListStateItemAction`<sup>[[?]](https://github.com/oroinc/OroCRMMailChimpBundle/tree/2.1.0/Model/Action/MarketingListStateItemAction.php "Oro\Bundle\MailChimpBundle\Model\Action\MarketingListStateItemAction")</sup>
    - changed the return type of `getMarketingListIterator` method from `BufferedQueryResultIterator` to `\Iterator`
