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

Install using Composer (use a tagged version instead of `dev-master` if one is available):

```
composer require jerive/scheduler-bundle dev-master
```

Add the bundle to `AppKernel.php`:

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

Update your Doctrine schema (you will need to use `--dump-sql` or `--force`, whichever you are comfortable with):

```
app/console doctrine:schema:update
```

Then run the `jerive:scheduler:execute` console command regularly, for example using `cron`. Make sure to use the `--quiet` option if you don’t want `cron` to send an e-mail every time a job is executed:

```
*/5 * * * * /path/to/my/project/app/console jerive:scheduler:execute --quiet
```
