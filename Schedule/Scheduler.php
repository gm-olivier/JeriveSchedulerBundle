<?php

namespace Jerive\Bundle\SchedulerBundle\Schedule;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Jerive\Bundle\SchedulerBundle\Entity\Job;
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
     * @param string $serviceId
     * @return \Jerive\Bundle\SchedulerBundle\Entity\Job
     * @throws \RuntimeException
     */
    public function createJob($serviceId, $name = null)
    {
        if (!$this->container->get($serviceId) instanceof ScheduledServiceInterface) {
            throw new \RuntimeException(sprintf('Service "%s" must implement ScheduledServiceInterface', $serviceId));
        }

        return (new Job())
            ->setServiceId($serviceId)
            ->setName($name)
            ->setProxy($this->container->get('jerive_scheduler.proxy'))
        ;
    }

    /**
     * @param Job $job
     */
    public function schedule(Job $job)
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $em->persist($job);
        $em->flush($job);
    }

    /**
     * Execute remaining jobs
     */
    public function executeJobs()
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $repository = $em->getRepository('JeriveSchedulerBundle:Job');
        $logger = $this->container->get('logger');

        foreach($repository->getExecutableJobs() as $job) {
            $job
                ->setStatus(Job::STATUS_PENDING)
                ->setLogger($logger)
            ;

            $em->persist($job);
            $em->flush($job);

            $logger->log(sprintf('Executing service "%s" on job "%s" #%s', $job->getServiceId(), $job->getName(), $job->getId()), \Monolog\Logger::INFO);
            $job->execute($this->container->get($job->getServiceId()));
            $em->persist($job);
            $em->flush($job);
        }
    }

    public function removeJobs()
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $repository = $em->getRepository('JeriveSchedulerBundle:Job');

        foreach($repository->getRemovableJobs() as $job) {
            $em->remove($job);
        }

        $em->flush();
    }
}
