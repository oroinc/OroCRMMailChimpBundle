<?php

namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Acl\Voter;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MailChimpBundle\Acl\Voter\EmailCampaignVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class EmailCampaignVoterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EmailCampaignVoter
     */
    protected $voter;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper
     */
    protected $doctrineHelper;

    protected function setUp()
    {
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->voter = new EmailCampaignVoter($this->doctrineHelper);
    }

    protected function tearDown()
    {
        unset($this->voter);
        unset($this->doctrineHelper);
    }

    /**
     * @dataProvider attributesDataProvider
     * @param array $attributes
     * @param EmailCampaign $emailCampaign
     * @param bool $expected
     */
    public function testVote($attributes, $emailCampaign, $expected)
    {
        $object = $this->getMockBuilder('Oro\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityClass')
            ->with($object)
            ->will($this->returnValue('stdClass'));
        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->with($object, false)
            ->will($this->returnValue(1));

        $this->assertEmailCampaignLoad($emailCampaign);
        $this->voter->setClassName('stdClass');

        /** @var \PHPUnit\Framework\MockObject\MockObject|TokenInterface $token */
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
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
     * @param EmailCampaign $emailCampaign
     */
    protected function assertEmailCampaignLoad(EmailCampaign $emailCampaign)
    {
        $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository
            ->expects($this->any())
            ->method('find')
            ->will($this->returnValue($emailCampaign));

        $this->doctrineHelper
            ->expects($this->any())
            ->method('getEntityRepository')
            ->will($this->returnValue($repository));
    }
}
