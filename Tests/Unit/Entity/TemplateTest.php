<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Entity;

use Oro\Bundle\MailChimpBundle\Entity\Template;

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
     * @param string $property
     * @param mixed $value
     *
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
     * @param string $property
     * @param mixed $value
     *
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
            ['channel', $this->createMock('Oro\\Bundle\\IntegrationBundle\\Entity\\Channel')],
            ['owner', $this->createMock('Oro\\Bundle\\OrganizationBundle\\Entity\\Organization')],
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
        $this->assertGreaterThanOrEqual($expectedUpdated, $this->target->getUpdatedAt());
        $this->assertNotSame($expectedUpdated, $this->target->getUpdatedAt());
    }

    public function testPreUpdate()
    {
        $this->assertNull($this->target->getUpdatedAt());
        $this->target->preUpdate();
        $this->assertInstanceOf('\DateTime', $this->target->getUpdatedAt());
    }
}
