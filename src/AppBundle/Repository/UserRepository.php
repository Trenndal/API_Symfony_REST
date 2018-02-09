<?php

namespace AppBundle\Repository;

class UserRepository extends AbstractRepository
{
    public function search($client, $term, $order = 'asc', $limit = 20, $offset = 0)
    {
        $qb = $this
            ->createQueryBuilder('a')
            ->select('a')
            ->where('a.client = ?1')
            ->orderBy('a.name', $order)
            ->setParameter(1, $client)
        ;

        if ($term) {
            $qb
                ->where('a.name LIKE ?1')
                ->setParameter(1, '%'.$term.'%')
            ;
        }

        return $this->paginate($qb, $limit, $offset);
    }
}
