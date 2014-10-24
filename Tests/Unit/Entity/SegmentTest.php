<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\PropertyAccess\PropertyAccess;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;
use OroCRM\Bundle\MailChimpBundle\Entity\Segment;

class SegmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Segment
     */
    protected $entity;

    public function setUp()
    {
        $this->entity = new Segment();
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
            ['marketingList', $this->getMock('OroCRM\\Bundle\\MarketingListBundle\\Entity\\MarketingList')],
            ['subscribersList', $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Entity\\SubscribersList')],
            ['subscribersList', $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Entity\\SubscribersList')],
            ['owner', $this->getMock('Oro\\Bundle\\OrganizationBundle\\Entity\\Organization')],
            ['syncStatus', 1],
            ['lastSynced', new \DateTime()],
            ['remoteRemove', true, false],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()],
        ];
    }

    public function testSegments()
    {
        $this->assertEquals(new ArrayCollection(), $this->entity->getMembers());
        $member = new Member();

        $this->entity->addMember($member);
        $this->assertEquals(new ArrayCollection([$member]), $this->entity->getMembers());

        $this->entity->removeMember($member);
        $this->assertEmpty($this->entity->getMembers());
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
}
