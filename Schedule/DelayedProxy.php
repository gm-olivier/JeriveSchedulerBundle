<?php

namespace Jerive\Bundle\SchedulerBundle\Schedule;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Jerive\Bundle\SchedulerBundle\Schedule\ScheduledServiceInterface;

/**
 * Description of DelayedProxy
 *
 * @author jerome
 */
class DelayedProxy implements \Serializable
{
    const PARAM_TYPE_STANDARD   = 0;

    const PARAM_TYPE_ENTITY     = 1;

    const PARAM_TYPE_SERIALIZED = 2;

    /**
     * @var array
     */
    protected $actions = array();

    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var object
     */
    protected $service;

    /**
     * @param Registry $doctrine
     * @return DelayedProxy
     */
    public function setDoctrine(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
        return $this;
    }

    public function serialize()
    {
        return serialize($this->actions);
    }

    public function unserialize($serialized)
    {
        $this->actions = unserialize($serialized);
    }

    public function reset()
    {
        $this->actions = array();
        return $this;
    }

    /**
     * @return DelayedProxy
     */
    public function setService(ScheduledServiceInterface $service)
    {
        $this->service = $service;
        return $this;
    }

    /**
     * @param ScheduledServiceInterface $service
     */
    public function execute()
    {
        foreach($this->actions as $action) {
            list($function, $params) = $action;
            foreach($params as &$param) {
                list($type, $realparam) = $param;
                if ($type == self::PARAM_TYPE_ENTITY) {
                    list($id, $class) = $realparam;
                    $param = $this->doctrine->getManager()->find($class, $id);
                } elseif ($type == self::PARAM_TYPE_STANDARD) {
                    $param = $realparam;
                } else {
                    $param = unserialize($realparam);
                }
            }

            call_user_func_array(array($this->service, $function), $params);
        }
    }

    /**
     *
     * @param string $function
     * @param array $params
     * @return \Jerive\Bundle\SchedulerBundle\Schedule\DelayedProxy
     * @throws \RuntimeException
     */
    public function __call($function, $params)
    {
        if (!method_exists($this->service, $function) && !method_exists($this->service, '__call')) {
            throw new \RuntimeException(sprintf('Service %s does not support method "%s"',  get_class($this->service), $function));
        }

        foreach($params as &$param) {
            if (is_resource($param)) {
                throw new \RuntimeException('Can not store resources');
            }

            if (is_object($param)) {
                if ($this->doctrine->getManager()->contains($param)) {
                    $class    = get_class($param);
                    $metadata = $this->doctrine->getManager()->getClassMetadata($class);
                    $param    = array(self::PARAM_TYPE_ENTITY, array(
                        $metadata->getIdentifierValues($param),
                        $class
                    ));
                } else {
                    $param = array(self::PARAM_TYPE_SERIALIZED, serialize($param));
                }
            } else {
                $param = array(self::PARAM_TYPE_STANDARD, $param);
            }
        }

        $this->actions[] = array($function, $params);

        return $this;
    }
}
