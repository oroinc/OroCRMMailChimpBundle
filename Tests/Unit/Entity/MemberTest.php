<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Entity;

use OroCRM\Bundle\MailChimpBundle\Entity\Member;

class MemberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Member
     */
    protected $target;

    public function setUp()
    {
        $this->target = new Member();
    }

    /**
     * @dataProvider settersAndGettersDataProvider
     */
    public function testSettersAndGetters($property, $value)
    {
        $method = 'set' . ucfirst($property);
        $result = $this->target->$method($value);

        $this->assertInstanceOf(get_class($this->target), $result);
        $this->assertEquals($value, $this->target->{'get' . $property}());
    }

    /**
     * @return array
     */
    public function settersAndGettersDataProvider()
    {
        return [
            ['originId', 123456789],
            ['channel', $this->getMock('Oro\\Bundle\\IntegrationBundle\\Entity\\Channel')],
            ['email', 'email@example.com'],
            ['phone', '555-666-777'],
            ['status', Member::STATUS_CLEANED],
            ['firstName', 'John'],
            ['lastName', 'Doe'],
            ['company', 'Doe Joe Ltd.'],
            ['memberRating', 2],
            ['optedInAt', new \DateTime()],
            ['optedInAt', null],
            ['optedInIpAddress', '5.6.7.8'],
            ['confirmedAt', new \DateTime()],
            ['confirmedIpAddress', null],
            ['latitude', '3910.57962'],
            ['longitude', '3910.57962'],
            ['gmtOffset', '3'],
            ['dstOffset', '3'],
            ['timezone', 'America/Los_Angeles'],
            ['cc', 'us'],
            ['region', 'ua'],
            ['lastChangedAt', new \DateTime()],
            ['lastChangedAt', null],
            ['euid', '123'],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()],
            ['updatedAt', null],
            ['marketingListItem', $this->getMock('OroCRM\\Bundle\\MarketingListBundle\\Entity\\MarketingListItem')],
            ['subscribersList', $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Entity\\SubscribersList')],
            ['mergeVarValues', ['Email Address' => 'test@example.com']],
            ['owner', $this->getMock('Oro\\Bundle\\OrganizationBundle\\Entity\\Organization')],
        ];
    }

    public function testPrePersist()
    {
        $this->assertNull($this->target->getCreatedAt());
        $this->assertNull($this->target->getUpdatedAt());

        $this->target->prePersist();

        $this->assertInstanceOf('\DateTime', $this->target->getCreatedAt());
        $this->assertInstanceOf('\DateTime', $this->target->getUpdatedAt());

        $expectedCreated = $this->target->getCreatedAt();
        $expectedUpdated = $this->target->getUpdatedAt();

        $this->target->prePersist();

        $this->assertSame($expectedCreated, $this->target->getCreatedAt());
        $this->assertSame($expectedUpdated, $this->target->getUpdatedAt());
    }

    public function testPreUpdate()
    {
        $this->assertNull($this->target->getUpdatedAt());
        $this->target->preUpdate();
        $this->assertInstanceOf('\DateTime', $this->target->getUpdatedAt());
    }
}
