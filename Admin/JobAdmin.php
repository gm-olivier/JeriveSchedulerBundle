<?php

namespace Jerive\Bundle\SchedulerBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;

use Jerive\Bundle\SchedulerBundle\Entity\Job;

class JobAdmin extends Admin
{
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('serviceId')
            ->add('tags', null, array(), null, array(
                //'expanded' => true,
                'multiple' => true,
            ))
            ->add('nextExecutionDate', 'doctrine_orm_date_range')
            ->add('status', 'doctrine_orm_choice', array(), 'choice',array(
                'choices' => array(
                    Job::STATUS_FAILED => 'Failed',
                    Job::STATUS_RUNNING => 'Running',
                    Job::STATUS_WAITING => 'Waiting',
                    Job::STATUS_TERMINATED => 'Ended',
                ),
            ))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('insertionDate')
            ->add('nextExecutionDate')
            ->add('executionCount')
            ->add('status')

        ;
    }
}
