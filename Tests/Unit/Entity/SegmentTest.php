<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Entity;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\MailChimpBundle\Entity\StaticSegment;
use Oro\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;

class SegmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StaticSegment
     */
    protected $entity;

    public function setUp()
    {
        $this->entity = new StaticSegment();
    }

    public function testId()
    {
        $this->assertNull($this->entity->getId());
    }

    /**
     * @dataProvider settersAndGettersDataProvider
     * @param string $property
     * @param mixed $value
     * @param mixed $default
     */
    public function testSettersAndGetters($property, $value, $default = null)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $this->assertEquals(
            $default,
            $propertyAccessor->getValue($this->entity, $property)
        );

        $propertyAccessor->setValue($this->entity, $property, $value);

        $this->assertEquals(
            $value,
            $propertyAccessor->getValue($this->entity, $property)
        );
    }

    /**
     * @return array
     */
    public function settersAndGettersDataProvider()
    {
        return [
            ['name', 'segment'],
            ['originId', 123456789],
            ['channel', $this->getMock('Oro\\Bundle\\IntegrationBundle\\Entity\\Channel')],
            ['marketingList', $this->getMock('Oro\\Bundle\\MarketingListBundle\\Entity\\MarketingList')],
            ['subscribersList', $this->getMock('Oro\\Bundle\\MailChimpBundle\\Entity\\SubscribersList')],
            ['subscribersList', $this->getMock('Oro\\Bundle\\MailChimpBundle\\Entity\\SubscribersList')],
            ['owner', $this->getMock('Oro\\Bundle\\OrganizationBundle\\Entity\\Organization')],
            ['syncStatus', 1],
            ['lastSynced', new \DateTime()],
            ['remoteRemove', true, false],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()],
        ];
    }

    public function testPrePersist()
    {
        $this->assertNull($this->entity->getCreatedAt());
        $this->assertNull($this->entity->getUpdatedAt());

        $this->entity->prePersist();

        $this->assertInstanceOf('\DateTime', $this->entity->getCreatedAt());
        $this->assertInstanceOf('\DateTime', $this->entity->getUpdatedAt());

        $expectedCreated = $this->entity->getCreatedAt();
        $expectedUpdated = $this->entity->getUpdatedAt();

        $this->entity->prePersist();

        $this->assertSame($expectedCreated, $this->entity->getCreatedAt());
        $this->assertSame($expectedUpdated, $this->entity->getUpdatedAt());
    }

    public function testPreUpdate()
    {
        $this->assertNull($this->entity->getUpdatedAt());
        $this->entity->preUpdate();
        $this->assertInstanceOf('\DateTime', $this->entity->getUpdatedAt());
    }

    public function testGetExtendedMergeVars()
    {
        $this->assertEmpty($this->entity->getExtendedMergeVars());

        $var = new ExtendedMergeVar();
        $this->entity->addExtendedMergeVar($var);

        $extendedMergeVars = $this->entity->getExtendedMergeVars();

        $this->assertNotEmpty($extendedMergeVars);
        $this->assertContains($var, $extendedMergeVars);

        $this->entity->removeExtendedMergeVar($var);

        $this->assertEmpty($this->entity->getExtendedMergeVars());
    }

    public function testGetExtendedMergeVarsWithFilterByState()
    {
        $this->assertEmpty($this->entity->getExtendedMergeVars());

        $var1 = new ExtendedMergeVar();
        $var2 = new ExtendedMergeVar();

        $var1->markSynced();
        $var2->markDropped();

        $this->entity->addExtendedMergeVar($var1);
        $this->entity->addExtendedMergeVar($var2);

        $extendedMergeVars = $this->entity->getExtendedMergeVars([ExtendedMergeVar::STATE_SYNCED]);

        $this->assertCount(1, $extendedMergeVars);
        $this->assertContainsOnly($var1, $extendedMergeVars);
    }

    public function testGetSyncedExtendedMergeVars()
    {
        $this->assertEmpty($this->entity->getExtendedMergeVars());

        $var1 = new ExtendedMergeVar();
        $var2 = new ExtendedMergeVar();

        $var1->markSynced();
        $var2->markDropped();

        $this->entity->addExtendedMergeVar($var1);
        $this->entity->addExtendedMergeVar($var2);

        $extendedMergeVars = $this->entity->getSyncedExtendedMergeVars();

        $this->assertCount(1, $extendedMergeVars);
        $this->assertContainsOnly($var1, $extendedMergeVars);
    }
}
