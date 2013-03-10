<?php

namespace Jerive\Bundle\SchedulerBundle\Schedule;

use Jerive\Bundle\SchedulerBundle\Entity\Task;

/**
 *
 * @author jerome
 */
interface ScheduledServiceInterface
{
    /**
     * @return DelayedProxy
     */
    public function setTask(Task $task);
}

