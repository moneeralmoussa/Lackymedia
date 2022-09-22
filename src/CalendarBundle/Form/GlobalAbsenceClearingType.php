<?php

namespace CalendarBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use EmployeeBundle\Entity\Department;
use EmployeeBundle\Entity\Employee;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class GlobalAbsenceClearingType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
        ->add('type', ChoiceType::class, [
              'mapped'=>false,
              'attr' => ['class' => 'type'],
                'choices' => [
                    '1' => 'Zuzügl.',
                    '2' => 'Abzügl.',
                ],
        ])
        ->add('day', null , array(
            'mapped'=>false,
            'required' => true,
            'data' => '1',
        ))
        ->add('note', TextareaType::class, array( 'mapped'=>false, 'required' => true))
        ->add('group', ChoiceType::class, [
              'mapped'=>false,
              'attr' => ['class' => 'group'],
                'choices' => [
                    '1' => 'Abteilung',
                    '2' => 'Mitarbeiter',
                ],
        ])
       ->add('employee', null, [
              'multiple' => true,
              'required' => false,
              'mapped' => false,
              'attr' => ['class' => 'employee'],
              'class' => Employee::class,
              'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where("u.deleted_at = '2500-12-31 00:00:00'")
                        ->orderBy('u.name', 'ASC');
              },
              'choice_label' => 'Fname',
              'choice_value' => 'id',
            ])
        ->add('departments', EntityType::class, [
            'mapped' => false,
            'multiple' => true,
            'required' => false,
            'attr' => ['class' => 'department'],
            'class' => Department::class,
            'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('u')
                      ->orderBy('u.name', 'ASC');
              },
              'choice_label' => 'name',
              'choice_value' => 'id',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            //'data_class' => 'AbsenceBundle\Entity\Absence',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'CalendarBundle_GlobalAbsenceClearing';
    }


}
