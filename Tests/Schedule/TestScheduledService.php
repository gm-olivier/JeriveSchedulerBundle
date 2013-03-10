<?php

namespace Jerive\Bundle\SchedulerBundle\Tests\Schedule;

use Jerive\Bundle\SchedulerBundle\Schedule\ScheduledServiceInterface;
use Jerive\Bundle\SchedulerBundle\Entity\Task;

/**
 * Description of TestScheduledService
 *
 * @author jerome
 */
class TestScheduledService implements ScheduledServiceInterface
{
    public function setTask(Task $task)
    {

    }

    public function log($str)
    {
        echo $str . "\n";
    }
}
