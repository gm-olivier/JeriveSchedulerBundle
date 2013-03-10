<?php

namespace Jerive\Bundle\SchedulerBundle\Entity;

use Jerive\Bundle\SchedulerBundle\Schedule\ScheduledServiceInterface;
use Jerive\Bundle\SchedulerBundle\Schedule\DelayedProxy;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Task
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $serviceId;

    /**
     * @var DelayedProxy
     *
     * @ORM\Column(type="object")
     */
    protected $proxy;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $nextExecutionDate;

    /**
     * A \DateInterval specification
     * http://www.php.net/manual/fr/dateinterval.construct.php
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $repeatEvery;

    /**
     * @ORM\Column(type="integer")
     */
    protected $executed = 0;

    public function __construct()
    {
        $this->date = new \DateTime('now');
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set service
     *
     * @param string $service
     * @return Task
     */
    public function setServiceId($service)
    {
        $this->serviceId = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return string
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * Set params
     *
     * @param string $params
     * @return Task
     */
    public function setProxy($proxy)
    {
        $this->params = $proxy;

        return $this;
    }

    /**
     * Get proxy
     *
     * @return string
     */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     *
     */
    public function execute(ScheduledServiceInterface $service)
    {
        if ($this->repeatEvery) {
            $this->setNextExecutionDate(
                (new \DateTime('now'))->add(new \DateInterval($this->repeatEvery))
            );
        }

        $this->executed++;

        $service->setTask($this);
        $this->getProxy()->execute($service);
    }

    /**
     * Set nextExecutionDate
     *
     * @param \DateTime $nextExecutionDate
     * @return Task
     */
    public function setNextExecutionDate($nextExecutionDate)
    {
        $this->nextExecutionDate = $nextExecutionDate;

        return $this;
    }

    /**
     * Get nextExecutionDate
     *
     * @return \DateTime
     */
    public function getNextExecutionDate()
    {
        return $this->nextExecutionDate;
    }

    /**
     * Set repeatEvery
     *
     * @param string $repeatEvery
     * @return Task
     */
    public function setRepeatEvery($repeatEvery)
    {
        new \DateInterval($repeatEvery);
        $this->repeatEvery = $repeatEvery;

        return $this;
    }

    /**
     * Get repeatEvery
     *
     * @return string
     */
    public function getRepeatEvery()
    {
        return $this->repeatEvery;
    }

    /**
     * Set executed
     *
     * @param \int $executed
     * @return Task
     */
    public function setExecuted($executed)
    {
        $this->executed = $executed;

        return $this;
    }

    /**
     * Get executed
     *
     * @return \int
     */
    public function getExecuted()
    {
        return $this->executed;
    }
}
