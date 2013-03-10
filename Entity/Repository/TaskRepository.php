<?php

use Doctrine\ORM\EntityRepository;

/**
 * Description of TaskRepository
 *
 * @author jerome
 */
class TaskRepository extends EntityRepository
{
    public function getPendingTasks()
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->where('t.nextExecutionDate < :date')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('executed', 0)
            ))
            ->setParameter('date', new DateTime('now'))
        ;

        return $qb->getQuery()->getResult();
    }
}
