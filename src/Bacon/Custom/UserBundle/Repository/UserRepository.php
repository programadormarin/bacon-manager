<?php

namespace Bacon\Custom\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Model\UserInterface;

class UserRepository extends EntityRepository
{
    public function getQueryPagination(UserInterface $entity,$sort,$direction)
    {
        $queryBuilder = $this->createQueryBuilder('u');

        $data = [
            'id'            => $entity->getId(),
            'username'      => $entity->getUsername(),
            'email'         => $entity->getEmail(),
            'enabled'       => $entity->isEnabled(),
        ];

        if (!empty($data['id'])) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->eq('u.id', ':id'))
                ->setParameter('id', $data['id'])
            ;
        }

        if (!empty($data['username'])) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->like('u.username', ':username'))
                ->setParameter('username', "%{$data['username']}%")
            ;
        }

        if (!empty($data['email'])) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->like('u.email', ':email'))
                ->setParameter('email', "%{$data['email']}%")
            ;
        }

        if (!empty($data['enabled'])) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->eq('u.enabled', ':enabled'))
                ->setParameter('enabled', $data['enabled'])
            ;
        }

        $queryBuilder->orderBy('u.' . $sort,$direction);

        return $queryBuilder->getQuery();
    }
}
