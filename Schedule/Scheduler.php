<?php

namespace Jerive\Bundle\SchedulerBundle\Schedule;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Monolog\Logger;
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

    /**
     * @var OutputInterface
     */
    protected $output;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function setOutputInterface(OutputInterface $output)
    {
        $this->output = $output;
        return $this;
    }

    protected function log($level, $message)
    {
        $logger = $this->container->get('logger');
        $logger->log($level, $message);

        if (isset($this->output)) {
            $this->output->writeln(sprintf('<info>%s</info>', $message));
        }
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

        return $job;
    }

    /**
     * Execute remaining jobs
     */
    public function executeJobs()
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $repository = $em->getRepository('JeriveSchedulerBundle:Job');

        foreach($repository->getExecutableJobs() as $job) {
            $job->prepareForExecution();
            $em->persist($job);
            $em->flush($job);

            try {
                $job->getProxy()->setDoctrine($this->container->get('doctrine'));
                $job->execute($this->container->get($job->getServiceId()));
            } catch (\Exception $e) {
                $this->log(Logger::ERROR, sprintf('FAILURE [%s] in job [%s]#%s', $job->getServiceId(), $job->getName(), $job->getId()));
            }

            $this->log(Logger::INFO, sprintf('SUCCESS [%s] in job [%s]#%s', $job->getServiceId(), $job->getName(), $job->getId()));

            $em->persist($job);
            $em->flush($job);
        }
    }

    public function cleanJobs()
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $repository = $em->getRepository('JeriveSchedulerBundle:Job');

        foreach($repository->getRemovableJobs() as $job) {
            $em->remove($job);
        }

        $em->flush();
    }
}
