<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Entity;

use OroCRM\Bundle\MailChimpBundle\Entity\MemberActivity;

class MemberActivityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MemberActivity
     */
    protected $target;

    public function setUp()
    {
        $this->target = new MemberActivity();
    }

    /**
     * @dataProvider settersAndGettersDataProvider
     * @param string $property
     * @param mixed $value
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
            ['channel', $this->getMock('Oro\Bundle\IntegrationBundle\Entity\Channel')],
            ['campaign', $this->getMock('OroCRM\Bundle\MailChimpBundle\Entity\Campaign')],
            ['member', $this->getMock('OroCRM\Bundle\MailChimpBundle\Entity\Member')],
            ['owner', $this->getMock('Oro\Bundle\OrganizationBundle\Entity\Organization')],
            ['email', 'test@test.com'],
            ['action', 'open'],
            ['ip', '127.0.0.1'],
            ['url', 'http://test.com'],
            ['activityTime', new \DateTime()],
        ];
    }
}
