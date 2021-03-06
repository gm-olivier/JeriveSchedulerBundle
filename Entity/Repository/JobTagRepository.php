<?php

namespace Jerive\Bundle\SchedulerBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Description of JobRepository
 *
 * @author jerome
 */
class JobTagRepository extends EntityRepository
{
    /**
     *
     * @param array $tags
     * @return array
     */
    public function getTagIds($tags)
    {
        $qb = $this->createQueryBuilder('t')->select('t.id');

        foreach($tags as $tag) {
            $conditions[] = $qb->expr()->eq('t.name', "'$tag'");
        }

        $qb->where(call_user_func_array(array($qb->expr(), 'orX'), $conditions));

        return $qb->getQuery()->getArrayResult();
    }
}
