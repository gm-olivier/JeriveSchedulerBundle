README
======

What is SchedulerBundle ?
-------------------------

It allows to schedule and execute jobs.

A job is the combination of:
  * a service ID
  * a list of methods applications along with its parameters, that can be
    either scalars or Doctrine entities. Doctrine entities are serialized
    as a class/ID pair, so as to be found later.

It is useful in the following cases:
  * you have a lot of different logics to execute remotely
  * the treatment of these jobs is not heavy

This bundle is meant to manage lightweight jobs.
If you want full control on the processes, great fault tolerance, use
https://github.com/schmittjoh/JMSJobQueueBundle/

This system does not serialize any Object, which can be a problem for some
complex cases, yet it is simple and lightweight and allows great flexibility.

###Features###

  * "Remote job programming"
  * Job tagging
  * Job execution logging
  * Job repetition

###Example###

``` php
<?php

$scheduler = $this->container->get('jerive_scheduler.scheduler');
$myJob     = $scheduler->createJob('my_company.my_scheduled_service');
$myJob
    ->setName('My first Job')
    ->setRepeatEvery('+7 days')
    ->setScheduledIn('+2 days')  // ->setScheduledAt((new \DateTime('now'))->modify('+2 days'))
    ->tag(array('my.first.job', 'user.reminder'))
    ->program()
        // Any method call and parameters will be recorded
        ->myMethod1(true)
        ->myMethod2(array(1, 2))
        ->sendReminderIfHasNotConfirmed($user)
;

$scheduler->schedule($myJob);
```

Installation
------------

``` php
<?php
    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Jerive\Bundle\SchedulerBundle\JeriveSchedulerBundle(),
            // ...
        );
    }
```
