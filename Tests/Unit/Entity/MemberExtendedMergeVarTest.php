<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Entity;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\MailChimpBundle\Entity\MemberExtendedMergeVar;

class MemberExtendedMergeVarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MemberExtendedMergeVar
     */
    protected $entity;

    protected function setUp()
    {
        $this->entity = new MemberExtendedMergeVar();
    }

    public function testObjectInitialization()
    {
        $entity = new MemberExtendedMergeVar();

        $this->assertEquals(MemberExtendedMergeVar::STATE_ADD, $entity->getState());
        $this->assertNull($entity->getStaticSegment());
        $this->assertNull($entity->getMember());
        $this->assertEmpty($entity->getMergeVarValues());
        $this->assertEmpty($entity->getMergeVarValuesContext());
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
            ['staticSegment', $this->createMock('Oro\\Bundle\\MailChimpBundle\\Entity\\StaticSegment')],
            ['state', MemberExtendedMergeVar::STATE_SYNCED, MemberExtendedMergeVar::STATE_ADD],
            ['merge_var_values', ['var' => 'value'], []],
            ['merge_var_values_context', ['context'], []]
        ];
    }

    public function testIsAddState()
    {
        $this->entity->setState(MemberExtendedMergeVar::STATE_ADD);
        $this->assertTrue($this->entity->isAddState());
    }

    public function testSetSyncedState()
    {
        $this->entity->markSynced();
        $this->assertEquals(MemberExtendedMergeVar::STATE_SYNCED, $this->entity->getState());
    }

    /**
     * @dataProvider invalidMergeVarNamesAndValues
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Merge name and value should be not empty strings.
     *
     * @param string $name
     * @param string $value
     */
    public function testAddMergeVarValueWhenNameOrValueIsInvalid($name, $value)
    {
        $this->entity->addMergeVarValue($name, $value);
    }

    /**
     * @return array
     */
    public function invalidMergeVarNamesAndValues()
    {
        return [
            ['', ''],
            ['', 'value'],
            ['name', ''],
            ['name', []],
            [[], 'value']
        ];
    }

    /**
     * @dataProvider validMergeVarNamesAndValues
     *
     * @param array $initialMergeVarValues
     * @param array $newMergeVarValues
     * @param string $initialState
     * @param string $expectedState
     */
    public function testAddMergeVarValue(
        array $initialMergeVarValues,
        array $newMergeVarValues,
        $initialState,
        $expectedState
    ) {
        foreach ($initialMergeVarValues as $name => $value) {
            $this->entity->addMergeVarValue($name, $value);
        }

        $this->entity->setState($initialState);

        foreach ($newMergeVarValues as $name => $value) {
            $this->entity->addMergeVarValue($name, $value);
        }

        $this->assertEquals($expectedState, $this->entity->getState());
    }

    /**
     * @return array
     */
    public function validMergeVarNamesAndValues()
    {
        return [
            [
                ['name' => 'value'], ['name_new' => 'value', 'name' => 'value_new'], 'sync', 'add'
            ],
            [
                ['name' => 'value'], ['name_new' => 'value'], 'sync', 'add'
            ],
            [
                ['name' => 'value'], ['name' => 'value_new'], 'sync', 'add'
            ],
            [
                ['name' => 'value'], ['name' => 'value'], 'sync', 'sync'
            ]
        ];
    }
}
