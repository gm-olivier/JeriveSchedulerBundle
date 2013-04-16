<?php

namespace Jerive\Bundle\SchedulerBundle\Schedule;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Monolog\Logger;
use Jerive\Bundle\SchedulerBundle\Entity\Job;
use Jerive\Bundle\SchedulerBundle\Entity\JobTag;
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
        $service = $this->container->get($serviceId);

        return (new Job())
            ->setServiceId($serviceId)
            ->setName($name)
            ->setProxy($this->container->get('jerive_scheduler.proxy')->setService($service))
        ;
    }

    /**
     * Process the tags added to the entity
     *
     * @param \Jerive\Bundle\SchedulerBundle\Entity\Job $job
     */
    protected function processTags(Job $job)
    {
        $names = array();
        $collection = $job->getTags();

        if ($collection->count()) {
            foreach($job->getTags() as $key => $tag) {
                if (!$tag->getId()) {
                    unset($collection[$key]);
                    $names[$tag->getName()] = $tag->getName();
                }
            }

            $qb = $this->container->get('doctrine')->getManager()->getRepository('JeriveSchedulerBundle:JobTag')->createQueryBuilder('t');
            $qb->where($qb->expr()->in('t.name', array_values($names)));

            foreach($qb->getQuery()->getResult() as $tag) {
                unset($names[$tag->getName()]);
                $collection->add($tag);
            }

            foreach($names as $name) {
                $collection->add((new JobTag)->setName($name));
            }
        }
    }

    /**
     * @param Job $job
     * @return Scheduler
     */
    public function schedule(Job $job)
    {
        $this->processTags($job);

        $this->getManager()->persist($job);
        $this->getManager()->flush($job);

        return $this;
    }

    /**
     * @return Scheduler
     */
    public function executeJobs()
    {
        foreach($this->getJobRepository()->getExecutableJobs() as $job) {
            $job->prepareForExecution();
            $this->getManager()->persist($job);
            $this->getManager()->flush($job);

            try {
                $job->getProxy()->setDoctrine($this->container->get('doctrine'));
                $job->execute($this->container->get($job->getServiceId()));
                $this->log(Logger::INFO, sprintf('SUCCESS [%s] in job [%s]#%s', $job->getServiceId(), $job->getName(), $job->getId()));
            } catch (\Exception $e) {
                $this->log(Logger::ERROR, sprintf('FAILURE [%s] in job [%s]#%s', $job->getServiceId(), $job->getName(), $job->getId()));
            }

            $this->getManager()->persist($job);
            $this->getManager()->flush($job);
        }

        return $this;
    }

    /**
     * @return Scheduler
     */
    public function cleanJobs()
    {
        foreach($this->getJobRepository()->getRemovableJobs() as $job) {
            $this->getManager()->remove($job);
            $this->log(Logger::INFO, sprintf('REMOVE job [%s]#%s', $job->getName(), $job->getId()));
        }

        $this->getManager()->flush();
        return $this;
    }

    /**
     *
     * @param array $tags
     * @param array $criteria
     * @return array
     */
    public function findByTags($tags, $criteria = array())
    {
        $names = array();
        foreach($tags as $tag) {
            $names[] = (string) $tag;
        }

        $qb = $this->getJobRepository()->createQueryBuilder('j');

        if (!empty($names)) {
            $qb
                ->leftJoin('j.tags', 't')
                ->where($qb->expr()->in('t.name', $names))
            ;
        }

        foreach($criteria as $key => $value) {
            $qb->andWhere('j.' . $key . ' = :' . $key)->setParameter($key, $value);
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return \Jerive\Bundle\SchedulerBundle\Entity\Repository\JobRepository
     */
    protected function getJobRepository()
    {
        return $this->getManager()->getRepository('JeriveSchedulerBundle:Job');
    }

    protected function getManager()
    {
        return $this->container->get('doctrine')->getManager();
    }
}
