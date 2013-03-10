<?php

namespace Jerive\Bundle\SchedulerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 *
 * @author jviveret
 */
class ExecuteCommand extends ContainerAwareCommand
{
    /**
     * @var \Jerive\Bundle\SchedulerBundle\Schedule\Scheduler
     */
    protected $scheduler;

    protected function configure()
    {
        $this
            ->setName('jerive:scheduler:execute')
            ->setHelp("Execute remaining jobs")
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('jerive_scheduler.scheduler')->executeJobs();
    }
}
