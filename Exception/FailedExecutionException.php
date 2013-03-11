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
    protected $trace;

    public function serialize()
    {
        $trace = array();
        foreach($this->getTrace() as $line) {
            unset($line['args']);
            $trace[] = $line;
        }

        return serialize(array(
            $this->code,
            $this->file,
            $this->line,
            $this->message,
            $trace,
        ));
    }

    public function unserialize($serialized)
    {
        list($this->code, $this->file, $this->line, $this->message, $this->trace) = unserialize($serialized);
    }

    public function getPreviousTrace()
    {
        return $this->trace;
    }
}
