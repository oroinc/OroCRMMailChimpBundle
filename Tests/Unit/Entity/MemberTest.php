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
        return array(
            array('originId', 123456789),
            array('channel', $this->getMock('Oro\\Bundle\\IntegrationBundle\\Entity\\Channel')),
            array('email', 'email@example.com'),
            array('status', Member::STATUS_CLEANED),
            array('firstName', 'John'),
            array('lastName', 'Doe'),
            array('company', 'Doe Joe Ltd.'),
            array('memberRating', 2),
            array('optedInAt', new \DateTime()),
            array('optedInAt', null),
            array('optedInIpAddress', '5.6.7.8'),
            array('confirmedAt', new \DateTime()),
            array('confirmedIpAddress', null),
            array('latitude', '3910.57962'),
            array('longitude', '3910.57962'),
            array('gmtOffset', '3'),
            array('dstOffset', '3'),
            array('timezone', 'America/Los_Angeles'),
            array('cc', 'us'),
            array('region', 'ua'),
            array('lastChangedAt', new \DateTime()),
            array('lastChangedAt', null),
            array('euid', '123'),
            array('createdAt', new \DateTime()),
            array('updatedAt', new \DateTime()),
            array('updatedAt', null),
            array('subscribersList', $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Entity\\SubscribersList')),
        );
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
