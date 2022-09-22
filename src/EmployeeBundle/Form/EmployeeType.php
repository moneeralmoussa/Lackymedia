<?php

namespace EmployeeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use EmployeeBundle\Entity\User\UserRepository;

class EmployeeType extends AbstractType
{
  protected $dse_roles;

  public function __construct($dse_roles) {
    $this->dse_roles = $dse_roles;
  }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

      $user = $options['user'];
        $builder
          ->add('trimbleId')
          ->add('countryCode')
          ->add('name')
          ->add('prename')
          ->add('salutation')
          ->add('street')
          ->add('zipCode')
          ->add('town')
          ->add('mobile')
          ->add('birthday', DateType::class, [
              'widget' => 'single_text',
              'format' => 'dd.MM.yyyy',
              'attr' => [
                  'class' => 'form-control input-inline datetimepicker',
                  'data-provide' => 'datepicker',
                  'data-date-format' => 'DD.MM.YYYY',
                  'data-date-default-date' => date('Y-m-d'),
              ],
              'html5' => false,
          ])
          ->add('entryDate', DateType::class, [
              'widget' => 'single_text',
              'format' => 'dd.MM.yyyy',
              'attr' => [
                  'class' => 'form-control input-inline datetimepicker',
                  'data-provide' => 'datepicker',
                  'data-date-format' => 'DD.MM.YYYY',
                  'data-date-default-date' => date('Y-m-d'),
              ],
              'html5' => false,
          ])
          // ->add('sex')
          ->add('initial')
          ->add('department')
          ->add('sleepsInCompanyMeansSleepsAtHome', HiddenType::class)
          ->add('usualHomeTravelHours', HiddenType::class)
          ->add('contract');
      if (in_array('ROLE_ADMIN',$user->getRoles())){
          $builder->add('salary')
          ->add('isUser', HiddenType::class, array('mapped' => false, 'required' => false))
          ->add('commentsalary' ,null, array('mapped' => false))
          ->add('fromdate', DateType::class, [
            'required' => true,
            'widget' => 'single_text',
            'format' => 'dd.MM.yyyy',
            'mapped' => false,
            'attr' => [
                'class' => 'form-control input-inline datetimepicker',
                'data-provide' => 'datepicker',
                'data-date-format' => 'DD.MM.YYYY',
                'data-date-default-date' => date('Y-m-d'),
            ],
            'html5' => false,
        ])
        ->add('todate', DateType::class, [
          'required' => false,
          'widget' => 'single_text',
          'format' => 'dd.MM.yyyy',
          'mapped' => false,
          'attr' => [
              'class' => 'form-control input-inline datetimepicker',
              'data-provide' => 'datepicker',
              'data-date-format' => 'DD.MM.YYYY',
             // 'data-date-default-date' => date('Y-m-d'),
          ],
          'html5' => false,
      ])
          ->add('username', null, array('required' => false))
          ->add('email', null, array('required' => false))
          ->add('password', PasswordType::class, array('required' => false))
          ->add('roles', ChoiceType::class, array(
            'choices' => $this->dse_roles,
            'choices_as_values' => true,
            'multiple' => true,
            'mapped'=>false,
            'required' => false
          ))
          ;
      }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EmployeeBundle\Entity\Employee',
            'user' => null,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'employeebundle_employee';
    }


}
