UPGRADE FROM 2.1 to 2.2
=======================

- Class `Oro\Bundle\MailChimpBundle\Async\ExportMailChimpProcessor`
    - construction signature was changed now it takes next arguments:
        - `DoctrineHelper` $doctrineHelper,
        - `ReverseSyncProcessor` $reverseSyncProcessor,
        - `StaticSegmentsMemberStateManager` $staticSegmentsMemberStateManager,
        - `JobRunner` $jobRunner,
        - `TokenStorageInterface` $tokenStorage,
        - `LoggerInterface` $logger

