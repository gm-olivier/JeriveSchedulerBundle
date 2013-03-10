<?php

namespace Jerive\Bundle\SchedulerBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Jerive\Bundle\SchedulerBundle\Entity\Job;

/**
 * Description of JobRepository
 *
 * @author jerome
 */
class JobRepository extends EntityRepository
{
    public function getExecutableJobs()
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->where('t.nextExecutionDate <= :date')
            ->andWhere('t.status = :status')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('t.executionCount', 0),
                $qb->expr()->isNotNull('t.repeatEvery')
            ))
            ->setParameters(array(
                'date'   => new \DateTime('now'),
                'status' => Job::STATUS_WAITING,
            ))
        ;

        return $qb->getQuery()->getResult();
    }

    public function getRemovableJobs()
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->where('t.status = :status')
            ->setParameters(array(
                'status' => Job::STATUS_TERMINATED,
            ))
        ;

        return $qb->getQuery()->getResult();
    }

    public function getFailedJobs()
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->where('t.status = :status')
            ->setParameters(array(
                'status' => Job::STATUS_PENDING,
            ))
        ;

        return $qb->getQuery()->getResult();
    }
}
