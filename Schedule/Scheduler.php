<?php

namespace Jerive\Bundle\SchedulerBundle\Schedule;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Jerive\Bundle\SchedulerBundle\Entity\Task;
use Jerive\Bundle\SchedulerBundle\Schedule\ScheduledServiceInterface;

/**
 * Description of Scheduler
 *
 * @author jerome
 */
class Scheduler implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     *
     * @param string $serviceId
     * @return \Jerive\Bundle\SchedulerBundle\Entity\Task
     * @throws \RuntimeException
     */
    public function createTask($serviceId)
    {
        $task = new Task();
        if (!$this->container->get($serviceId) instanceof ScheduledServiceInterface) {
            throw new \RuntimeException(sprintf('Service "%s" must implement ScheduledServiceInterface', $serviceId));
        }
        $task->setServiceId($serviceId);
        $task->setProxy($this->container->get('jerive_scheduler.proxy'));
        $task->setNextExecutionDate(new \DateTime('now'));

        return $task;
    }

    /**
     * @param Task $task
     */
    public function schedule(Task $task)
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $em->persist($task);
        $em->flush($task);
    }

    /**
     * Execute remaining tasks
     */
    public function executeTasks()
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $repository = $em->getRepository('JeriveSchedulerBundle:Task');

        foreach($repository->getPendingTasks() as $task) {
            $task->execute($this->container->get($task->getServiceId()));
            $em->persist($task);
        }

        $em->flush();
    }
}
