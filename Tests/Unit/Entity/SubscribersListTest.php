<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Entity;

use Oro\Bundle\MailChimpBundle\Entity\SubscribersList;
use Oro\Bundle\MailChimpBundle\Model\MergeVar\MergeVarFieldsInterface;

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
     * @dataProvider settersAndIsDataProvider
     */
    public function testSettersAndIs($property, $value)
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
            ['webId', 12],
            ['name', 'string'],
            ['defaultFromName', 'string'],
            ['defaultFromName', null],
            ['defaultFromEmail', 'string'],
            ['defaultFromEmail', null],
            ['defaultSubject', 'string'],
            ['defaultSubject', null],
            ['defaultLanguage', 'string'],
            ['defaultLanguage', null],
            ['listRating', 1.3],
            ['listRating', null],
            ['subscribeUrlShort', "string"],
            ['subscribeUrlShort', null],
            ['subscribeUrlLong', "string"],
            ['subscribeUrlLong', null],
            ['beamerAddress', "string"],
            ['beamerAddress', null],
            ['visibility', "string"],
            ['visibility', null],
            ['memberCount', 3.4],
            ['memberCount', null],
            ['unsubscribeCount', 4.4],
            ['unsubscribeCount', null],
            ['cleanedCount', 43.4],
            ['cleanedCount', null],
            ['memberCountSinceSend', 433.4],
            ['memberCountSinceSend', null],
            ['unsubscribeCountSinceSend', 33.4],
            ['unsubscribeCountSinceSend', null],
            ['cleanedCountSinceSend', 333.4],
            ['cleanedCountSinceSend', null],
            ['campaignCount', 13.43],
            ['campaignCount', null],
            ['groupingCount', 123.43],
            ['groupingCount', null],
            ['groupCount', 4321.43],
            ['groupCount', null],
            ['mergeVarCount', 41.43],
            ['mergeVarCount', null],
            ['avgSubRate', 87.43],
            ['avgSubRate', null],
            ['avgUsubRate', 97.43],
            ['avgUsubRate', null],
            ['targetSubRate', 7.12],
            ['targetSubRate', null],
            ['openRate', 72.12],
            ['openRate', null],
            ['clickRate', 2.12],
            ['clickRate', null],
            [
                'mergeVarFields',
                $this->createMock('Oro\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarFieldsInterface')
            ],
            ['mergeVarConfig', [['foo' => 'bar']]],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()],
            ['updatedAt', null],
        ];
    }

    /**
     * @return array
     */
    public function settersAndIsDataProvider()
    {
        return [
            ['emailTypeOption', true],
            ['useAwesomeBar', true],
        ];
    }

    public function testSetMergeVarConfigResetsMergeVarFields()
    {
        /** @var MergeVarFieldsInterface|\PHPUnit_Framework_MockObject_MockObject $mergeVarsFields */
        $mergeVarsFields = $this->createMock('Oro\\Bundle\\MailChimpBundle\\Model\\MergeVar\\MergeVarFieldsInterface');

        $this->target->setMergeVarFields($mergeVarsFields);

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
