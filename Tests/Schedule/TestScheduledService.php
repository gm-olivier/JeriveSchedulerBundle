<?php

namespace Jerive\Bundle\SchedulerBundle\Tests\Schedule;

use Jerive\Bundle\SchedulerBundle\Schedule\ScheduledServiceInterface;

/**
 * Description of TestScheduledService
 *
 * @author jerome
 */
class TestScheduledService implements ScheduledServiceInterface
{
    use \Jerive\Bundle\SchedulerBundle\Schedule\ScheduledServiceTrait;

    public function log($str)
    {
        echo $str . "\n";
    }

    public function getInfo()
    {
        $this->log('Id:    ' . $this->job->getId());
        $this->log('Count: ' . $this->job->getExecutionCount());
    }
}
