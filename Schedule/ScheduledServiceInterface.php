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
     *
     * @param Job $job
     */
    public function setScheduledJob(Job $job);
}

