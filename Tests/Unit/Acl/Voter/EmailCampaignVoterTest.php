<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Unit\Acl\Voter;

use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\MailChimpBundle\Acl\Voter\EmailCampaignVoter;

class EmailCampaignVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailCampaignVoter
     */
    protected $voter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrineHelper;

    protected function setUp()
    {
        $this->registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->voter = new EmailCampaignVoter($this->registry, $this->doctrineHelper);
    }

    protected function tearDown()
    {
        unset($this->voter);
        unset($this->registry);
        unset($this->doctrineHelper);
    }

    /**
     * @param string $attribute
     * @param bool $expected
     * @dataProvider supportsAttributeDataProvider
     */
    public function testSupportsAttribute($attribute, $expected)
    {
        $this->assertEquals($expected, $this->voter->supportsAttribute($attribute));
    }

    /**
     * @return array
     */
    public function supportsAttributeDataProvider()
    {
        return [
            'VIEW'   => ['VIEW', false],
            'CREATE' => ['CREATE', false],
            'EDIT'   => ['EDIT', true],
            'DELETE' => ['DELETE', false],
            'ASSIGN' => ['ASSIGN', false],
        ];
    }

    /**
     * @param string $class
     * @param bool $expected
     * @dataProvider supportsClassDataProvider
     */
    public function testSupportsClass($class, $expected)
    {
        $this->assertEquals($expected, $this->voter->supportsClass($class));
    }

    /**
     * @return array
     */
    public function supportsClassDataProvider()
    {
        return [
            'supported class' => [EmailCampaignVoter::ENTITY, true],
            'not supported class' => ['NotSupportedClass', false],
        ];
    }

    /**
     * @dataProvider attributesDataProvider
     * @param array $attributes
     * @param $emailCampaign
     * @param $expected
     */
    public function testVote($attributes, $emailCampaign, $expected)
    {
        $object = $this->getMockBuilder('Oro\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityClass')
            ->with($object)
            ->will($this->returnValue(EmailCampaignVoter::ENTITY));
        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->with($object, false)
            ->will($this->returnValue(1));

        if ($this->voter->supportsAttribute($attributes[0])) {
            $this->assertEmailCampaignLoad($emailCampaign);
        }

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $this->assertEquals(
            $expected,
            $this->voter->vote($token, $object, $attributes)
        );
    }

    /**
     * @return array
     */
    public function attributesDataProvider()
    {
        $emailCampaignNew = new EmailCampaign();
        $emailCampaignSent = new EmailCampaign();
        $emailCampaignSent->setSent(true);

        return [
            [['VIEW'], $emailCampaignNew, EmailCampaignVoter::ACCESS_ABSTAIN],
            [['CREATE'], $emailCampaignNew, EmailCampaignVoter::ACCESS_ABSTAIN],
            [['EDIT'], $emailCampaignNew, EmailCampaignVoter::ACCESS_ABSTAIN],
            [['DELETE'], $emailCampaignNew, EmailCampaignVoter::ACCESS_ABSTAIN],
            [['ASSIGN'], $emailCampaignNew, EmailCampaignVoter::ACCESS_ABSTAIN],
            [['VIEW'], $emailCampaignSent, EmailCampaignVoter::ACCESS_ABSTAIN],
            [['CREATE'], $emailCampaignSent, EmailCampaignVoter::ACCESS_ABSTAIN],
            [['EDIT'], $emailCampaignSent, EmailCampaignVoter::ACCESS_DENIED],
            [['DELETE'], $emailCampaignSent, EmailCampaignVoter::ACCESS_ABSTAIN],
            [['ASSIGN'], $emailCampaignSent, EmailCampaignVoter::ACCESS_ABSTAIN],
        ];
    }

    /**
     * @param $emailCampaign
     */
    protected function assertEmailCampaignLoad($emailCampaign)
    {
        $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('find')
            ->will($this->returnValue($emailCampaign));
        $em = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with('OroCRMCampaignBundle:EmailCampaign')
            ->will($this->returnValue($repository));
        $this->registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($em));
    }
}
