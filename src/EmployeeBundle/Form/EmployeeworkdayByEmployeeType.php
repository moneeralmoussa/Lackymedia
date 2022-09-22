<?php

namespace EmployeeBundle\Form;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use EmployeeBundle\Entity\User\UserRepository;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;



class EmployeeworkdayByEmployeeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    { 
        $builder
          ->add('begin_employeeposition_name', null , [
            'mapped' => false,
          ])
         
          ->add('begin_employeeposition_date', DateTimeType::class, [
            'mapped'=>true,
            'widget' => 'single_text',
            'format' => 'dd.MM.yyyy HH:mm',
            'attr' => [
                'class' => 'form-control input-inline datetimepicker',
                'data-provide' => 'datepicker',
                'data-date-format' => 'DD.MM.YYYY HH:mm',
                'data-date-default-date' => date('Y-m-d H:i'),
            ],
            'html5' => false,
            
        ])
          ->add('end_employeeposition_name', null , [
              'mapped' => false,
            ])
         
            ->add('end_employeeposition_date', DateTimeType::class, [
              'mapped'=>true,
              'widget' => 'single_text',
              'format' => 'dd.MM.yyyy HH:mm',
              'attr' => [
                  'class' => 'form-control input-inline datetimepicker',
                  'data-provide' => 'datepicker',
                  'data-date-format' => 'DD.MM.YYYY HH:mm',
                  'data-date-default-date' => date('Y-m-d H:i'),
              ],
              'html5' => false,
          ])
         ;
    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EmployeeBundle\Entity\Employeeworkday'
        ));
    }
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'employeebundle_employeeworkday';
    }
}
