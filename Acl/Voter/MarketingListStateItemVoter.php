<?php

namespace OroCRM\Bundle\MailChimpBundle\Acl\Voter;

use Doctrine\ORM\Query\Expr\Join;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\Acl\Voter\AbstractEntityVoter;
use OroCRM\Bundle\MailChimpBundle\Model\FieldHelper;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListStateItemInterface;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;

class MarketingListStateItemVoter extends AbstractEntityVoter
{
    /**
     * @var array
     */
    protected $supportedAttributes = ['DELETE'];

    /**
     * @var ContactInformationFieldsProvider
     */
    protected $contactInformationFieldsProvider;

    /**
     * @var FieldHelper
     */
    protected $fieldHelper;

    /**
     * @var string
     */
    protected $memberClassName;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param ContactInformationFieldsProvider $contactInformationFieldsProvider
     * @param FieldHelper $fieldHelper
     * @param string $memberClassName
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        ContactInformationFieldsProvider $contactInformationFieldsProvider,
        FieldHelper $fieldHelper,
        $memberClassName
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->contactInformationFieldsProvider = $contactInformationFieldsProvider;
        $this->fieldHelper = $fieldHelper;
        $this->memberClassName = $memberClassName;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPermissionForAttribute($class, $identifier, $attribute)
    {
        /** @var MarketingListStateItemInterface $item */
        $item = $this->doctrineHelper->getRepository($this->className)->find($identifier);
        $entityClass = $item->getMarketingList()->getEntity();
        $entity = $this->doctrineHelper->getRepository($entityClass)->find($item->getEntityId());

        $contactInformationFields = $this->contactInformationFieldsProvider->getEntityTypedFields(
            $entity,
            ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
        );

        $contactInformationValues = $this->contactInformationFieldsProvider->getTypedFieldsValues(
            $contactInformationFields,
            $entity
        );

        $memberContactInformationFields = $this->contactInformationFieldsProvider->getEntityTypedFields(
            $this->memberClassName,
            ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
        );

        $qb = $this->doctrineHelper
            ->getEntityManager($this->memberClassName)
            ->createQueryBuilder();

        $expr = $qb->expr()->orX();
        foreach ($memberContactInformationFields as $memberContactInformationField) {
            $expr->add(
                $qb->expr()->in(
                    sprintf('mmb.%s', $memberContactInformationField),
                    $contactInformationValues
                )
            );
        }


        /** @todo: marketing list and member status */
        $qb
            ->select('COUNT(mmb.id)')
            ->from('OroCRMMailChimpBundle:SubscribersList', 'subscribersList')
            ->join(
                $this->memberClassName,
                'mmb',
                Join::WITH,
                'mmb.subscribersList = subscribersList.id'
            )
            ->join(
                'OroCRMMarketingListBundle:MarketingList',
                'ml',
                Join::WITH,
                'mmb.subscribersList = subscribersList.id'
            )
            ->where($expr)
            ->groupBy('mmb.id');


        $memberExists = $qb->getQuery()->getResult();

        if ($memberExists) {
            return self::ACCESS_DENIED;
        }

        return self::ACCESS_ABSTAIN;
    }
}
