<?php

namespace Jerive\Bundle\SchedulerBundle\Exception;

/**
 * Description of FailedExecutionException
 * Based on Fabpot's article
 * http://fabien.potencier.org/article/9/php-serialization-stack-traces-and-exceptions
 *
 * @author jerome
 */
class FailedExecutionException extends \Exception implements \Serializable
{
    protected $traceAsString;

    public function serialize()
    {
        return serialize(array(
            $this->code,
            $this->file,
            $this->line,
            $this->message,
            $this->getTraceAsString(),
        ));
    }

    public function unserialize($serialized)
    {
        list($this->code, $this->file, $this->line, $this->message, $this->traceAsString) = unserialize($serialized);
    }

    public function getTraceAsString()
    {
        return $this->traceAsString;
    }
}
