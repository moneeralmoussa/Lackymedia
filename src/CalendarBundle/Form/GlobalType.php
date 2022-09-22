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

class GlobalType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
        ->add('fromDate', DateTimeType::class, [
            'mapped'=>false,
            'widget' => 'single_text',
            'format' => 'dd.MM.yyyy',
            'attr' => [
                'class' => 'form-control input-inline datetimepicker',
                'data-provide' => 'datepicker',
                'data-date-format' => 'DD.MM.YYYY',
                'data-date-default-date' => date('Y-m-d'),
                'readonly' => 'readonly'
            ],
            'html5' => false,
        ])
        ->add('toDate', DateTimeType::class, [
            'mapped'=>false,
            'widget' => 'single_text',
            'format' => 'dd.MM.yyyy',
            'attr' => [
                'class' => 'form-control input-inline datetimepicker',
                'data-provide' => 'datepicker',
                'data-date-format' => 'DD.MM.YYYY',
                'data-date-default-date' => date('Y-m-d'),
                'readonly' => 'readonly'
            ],
            'html5' => false,
        ])
        ->add('day',TextType::class, array(
            'mapped'=>true,
            'required' => false,
            'attr' => array(
              'readonly' => true,
            ),
            'data' => '1',
        ))
        ->add('reason',NULL, ['required' => false ,'attr' => ['class' => 'type']])
        ->add('sendInfo', CheckboxType::class, array('mapped' => false ,'required' => false, 'attr' => ['checked'=> true] ))
        ->add('note', TextareaType::class, array('required' => false))
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
            'data_class' => 'AbsenceBundle\Entity\Absence',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'CalendarBundle_Global';
    }


}
