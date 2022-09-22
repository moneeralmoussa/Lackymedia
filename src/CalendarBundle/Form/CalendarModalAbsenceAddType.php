<?php
namespace CalendarBundle\Form;

use AbsenceBundle\Entity\Absence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CalendarModalAbsenceAddType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

      $user = $options['user'];
      $builder
      ->add('day')
      ->add('reason');
      if (in_array('ROLE_ADMIN',$user->getRoles()) || in_array('ROLE_HOLIDAY',$user->getRoles())){
        $builder->add('status');
        $builder->add('sendInfo', CheckboxType::class, array('mapped' => false ,'required' => false, 'attr' => ['checked'=> true] ));
      }
      $builder->add('note',TextareaType::class, array('required' => false));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Absence::class,
            'user' => null
        ));
    }
}
