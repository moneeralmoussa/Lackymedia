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

class NewEmployeeType extends AbstractType
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
           ->add('trimbleId' ,null, array('required' => true))
           ->add('name',null, array('required' => true))
           ->add('prename',null, array('required' => true))
           ->add('salutation',null, array('required' => true))
           ->add('street',null, array('required' => true))
           ->add('zipCode',null, array('required' => true))
           ->add('town',null, array('required' => true))
           ->add('countryCode',null, array('required' => true))
           ->add('mobile',null, array('required' => true))
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
               'mapped' => true,
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
           ->add('initial',null, array('required' => true))
           ->add('department',null, array('required' => true))
           ->add('sleepsInCompanyMeansSleepsAtHome', HiddenType::class)
           ->add('usualHomeTravelHours', HiddenType::class)
           ->add('contract',null, array('required' => true));
       if (in_array('ROLE_ADMIN',$user->getRoles())){
           $builder->add('isUser', HiddenType::class, array('mapped' => false, 'required' => false))
           /*
           ->add('username', null, array('required' => true))
           ->add('email', null, array('required' => true))
           ->add('password', PasswordType::class, array('required' => true))
           */
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
