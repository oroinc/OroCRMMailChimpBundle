<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Entity;

use OroCRM\Bundle\MailChimpBundle\Entity\Template;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Template
     */
    protected $target;

    public function setUp()
    {
        $this->target = new Template();
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
     * @dataProvider boolSettersAndGettersDataProvider
     */
    public function testBoolSettersAndGetters($property, $value)
    {
        $method = 'set' . ucfirst($property);
        $result = $this->target->$method($value);

        $this->assertInstanceOf(get_class($this->target), $result);
        $this->assertEquals($value, $this->target->{'is' . $property}());
    }

    /**
     * @return array
     */
    public function settersAndGettersDataProvider()
    {
        return [
            ['originId', 123456789],
            ['channel', $this->getMock('Oro\\Bundle\\IntegrationBundle\\Entity\\Channel')],
            ['owner', $this->getMock('Oro\\Bundle\\OrganizationBundle\\Entity\\Organization')],
            ['type', Template::TYPE_USER],
            ['name', 'String'],
            ['layout', 'Text'],
            ['layout', null],
            ['category', 'String'],
            ['category', null],
            ['previewImage', 'Text'],
            ['previewImage', null],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()],
        ];
    }

    /**
     * @return array
     */
    public function boolSettersAndGettersDataProvider()
    {
        return [
            ['active', true],
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
    }

    public function testPreUpdate()
    {
        $this->assertNull($this->target->getUpdatedAt());
        $this->target->preUpdate();
        $this->assertInstanceOf('\DateTime', $this->target->getUpdatedAt());
    }
}
