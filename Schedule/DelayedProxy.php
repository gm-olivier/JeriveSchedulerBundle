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
    const PARAM_TYPE_STANDARD = 0;

    const PARAM_TYPE_ENTITY   = 1;

    protected $actions = array();

    /**
     * @var Registry
     */
    protected $doctrine;

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

    /**
     * @param ScheduledServiceInterface $service
     */
    public function execute(ScheduledServiceInterface $service)
    {
        foreach($this->actions as $action) {
            list($function, $params) = $action;
            foreach($params as &$param) {
                list($type, $realparam) = $param;
                if ($type == self::PARAM_TYPE_ENTITY) {
                    list($id, $class) = $realparam;
                    $param = $this->doctrine->getManager()->find($class, $id);
                } else {
                    $param = $realparam;
                }
            }

            call_user_func_array(array($service, $function), $params);
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
                    throw new \RuntimeException('Can only store entities managed by doctrine');
                }
            } else {
                $param = array(self::PARAM_TYPE_STANDARD, $param);
            }
        }

        $this->actions[] = array($function, $params);

        return $this;
    }
}
