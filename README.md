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

Features:
  * Logging
  * Repetition management
  * Tagging management
  * Fault tolerant

