<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Entity;

use Symfony\Component\PropertyAccess\PropertyAccess;

use OroCRM\Bundle\MailChimpBundle\Entity\ExtendedMergeVar;

class ExtendedMergeVarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExtendedMergeVar
     */
    protected $entity;

    protected function setUp()
    {
        $this->entity = new ExtendedMergeVar();
    }

    public function testObjectInitialization()
    {
        $entity = new ExtendedMergeVar();

        $this->assertEquals(ExtendedMergeVar::STATE_ADD, $entity->getState());
        $this->assertEquals(ExtendedMergeVar::TAG_TEXT_FIELD_TYPE, $entity->getFieldType());
        $this->assertFalse($entity->isRequired());
        $this->assertNull($entity->getName());
        $this->assertNull($entity->getLabel());
        $this->assertNull($entity->getTag());
    }

    public function testGetId()
    {
        $this->assertNull($this->entity->getId());

        $value = 8;
        $idReflection = new \ReflectionProperty(get_class($this->entity), 'id');
        $idReflection->setAccessible(true);
        $idReflection->setValue($this->entity, $value);
        $this->assertEquals($value, $this->entity->getId());
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
            ['staticSegment', $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Entity\\StaticSegment')],
            ['label', 'Dummy Label'],
            ['state', ExtendedMergeVar::STATE_SYNCED, ExtendedMergeVar::STATE_ADD]
        ];
    }

    /**
     * @dataProvider setNameDataProvider
     * @param mixed $value
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Name must be not empty string.
     */
    public function testSetNameWhenInputIsWrong($value)
    {
        $this->entity->setName($value);
    }

    /**
     * @return array
     */
    public function setNameDataProvider()
    {
        return [
            [''],
            [123],
            [[]],
            [new \ArrayIterator([])]
        ];
    }

    public function testSetAndGetName()
    {
        $this->assertNull($this->entity->getName());
        $name = 'total';
        $expectedTag = ExtendedMergeVar::TAG_PREFIX . strtoupper($name);
        $this->entity->setName($name);

        $this->assertEquals($name, $this->entity->getName());
        $this->assertEquals($expectedTag, $this->entity->getTag());
    }

    /**
     * @dataProvider tagGenerationDataProvider
     * @param string $value
     * @param string $expected
     */
    public function testTagGenerationWithDifferentNameLength($value, $expected)
    {
        $this->entity->setName($value);

        $this->assertEquals($expected, $this->entity->getTag());
    }

    /**
     * @return array
     */
    public function tagGenerationDataProvider()
    {
        return [
            ['total', ExtendedMergeVar::TAG_PREFIX . 'TOTAL'],
            ['entity_total', ExtendedMergeVar::TAG_PREFIX . 'NTTY_TTL'],
            ['anyEntityAttr', ExtendedMergeVar::TAG_PREFIX . 'NYNTTYTT']
        ];
    }

    public function testIsAddState()
    {
        $this->entity->setState(ExtendedMergeVar::STATE_ADD);
        $this->assertTrue($this->entity->isAddState());
    }

    public function testIsRemoveState()
    {
        $this->entity->setState(ExtendedMergeVar::STATE_REMOVE);
        $this->assertTrue($this->entity->isRemoveState());
    }

    public function testSetSyncedState()
    {
        $this->entity->markSynced();
        $this->assertEquals(ExtendedMergeVar::STATE_SYNCED, $this->entity->getState());
    }

    public function testSetDroppedState()
    {
        $this->entity->markDropped();
        $this->assertEquals(ExtendedMergeVar::STATE_DROPPED, $this->entity->getState());
    }
}
