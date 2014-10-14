<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Entity;

use OroCRM\Bundle\MailChimpBundle\Entity\SubscribersList;

class SubscribersListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SubscribersList
     */
    protected $target;

    public function setUp()
    {
        $this->target = new SubscribersList();
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
            [
                'mergeVarFields',
                $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarFieldsInterface')
            ],
            ['mergeVarConfig', [['foo' => 'bar']]],
            ['owner', $this->getMock('Oro\\Bundle\\OrganizationBundle\\Entity\\Organization')],
        ];
    }

    public function testSetMergeVarConfigResetsMergeVarFields()
    {
        $this->target->setMergeVarFields(
            $this->getMock('OroCRM\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarFieldsInterface')
        );

        $this->target->setMergeVarConfig([]);

        $this->assertNull($this->target->getMergeVarFields());
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
