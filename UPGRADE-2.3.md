UPGRADE FROM 2.2 to 2.3
========================

MailChimpBundle
---------------
* The `AbstractStaticSegmentIterator::__construct(MarketingListProvider $marketingListProvider, OwnershipMetadataProvider $ownershipMetadataProvider, $removedItemClassName, $unsubscribedItemClassName)`<sup>[[?]](https://github.com/oroinc/OroCRMMailChimpBundle/tree/2.2.0/Provider/Transport/Iterator/AbstractStaticSegmentIterator.php#L50 "Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator\AbstractStaticSegmentIterator")</sup> method was changed to `AbstractStaticSegmentIterator::__construct(MarketingListProvider $marketingListProvider, OwnershipMetadataProviderInterface $ownershipMetadataProvider, $removedItemClassName, $unsubscribedItemClassName)`<sup>[[?]](https://github.com/oroinc/OroCRMMailChimpBundle/tree/2.3.0/Provider/Transport/Iterator/AbstractStaticSegmentIterator.php#L50 "Oro\Bundle\MailChimpBundle\Provider\Transport\Iterator\AbstractStaticSegmentIterator")</sup>
* The `ExportMailChimpProcessor::processMessageData(array $body, $integration)`<sup>[[?]](https://github.com/oroinc/OroCRMMailChimpBundle/tree/2.2.0/Async/ExportMailChimpProcessor.php#L138 "Oro\Bundle\MailChimpBundle\Async\ExportMailChimpProcessor")</sup> method was changed to `ExportMailChimpProcessor::processMessageData(array $body, Channel $integration)`<sup>[[?]](https://github.com/oroinc/OroCRMMailChimpBundle/tree/2.3.0/Async/ExportMailChimpProcessor.php#L140 "Oro\Bundle\MailChimpBundle\Async\ExportMailChimpProcessor")</sup>

