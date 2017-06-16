UPGRADE FROM 2.2 to 2.3
=======================

- Class `Oro\Bundle\MailChimpBundle\Async\ExportMailChimpProcessor`
    - changed method `processMessageData` signature from `processMessageData(array $body, $integration)` to `processImport(Integration $integration, ConnectorInterface $connector, array $configuration)`
