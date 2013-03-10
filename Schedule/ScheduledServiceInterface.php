<?php

namespace Jerive\Bundle\SchedulerBundle\Schedule;

use Jerive\Bundle\SchedulerBundle\Entity\Job;

/**
 *
 * @author jerome
 */
interface ScheduledServiceInterface
{
    /**
     * @return DelayedProxy
     */
    public function setJob(Job $job);
}

