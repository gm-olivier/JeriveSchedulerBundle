<?php

namespace Jerive\Bundle\SchedulerBundle\Tests\Schedule;

use Jerive\Bundle\SchedulerBundle\Schedule\ScheduledServiceInterface;
use Jerive\Bundle\SchedulerBundle\Entity\Job;

/**
 * Description of TestScheduledService
 *
 * @author jerome
 */
class TestScheduledService implements ScheduledServiceInterface
{
    /**
     * @var Job
     */
    protected $job;

    public function setJob(Job $job)
    {
        $this->job = $job;
    }

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
