<?php

namespace Jerive\Bundle\SchedulerBundle\Tests\Schedule;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultTest extends WebTestCase
{
    public function testIndex()
    {
        $client = $this->createClient();
        $container = $client->getContainer();

        $this->scheduler = $container->get('jerive_scheduler.scheduler');
        $job = $this->scheduler->createJob('jerive_scheduler.test_service');
        $job
            ->setRepeatEvery('PT1M')
            ->program()
            ->log('toto')
            ->log('coco')
        ;

        $this->scheduler->schedule($job);
    }
}
