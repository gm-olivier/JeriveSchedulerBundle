<?php

namespace Jerive\Bundle\SchedulerBundle\Schedule;

use Jerive\Bundle\SchedulerBundle\Entity\Job;

/**
 * Description of ScheduledServiceTrait
 *
 * @author jerome
 */
trait ScheduledServiceTrait
{
    /**
     * @var Job
     */
    protected $job;

    public function setJob(Job $job)
    {
        $this->job = $job;

        return $this;
    }
}
