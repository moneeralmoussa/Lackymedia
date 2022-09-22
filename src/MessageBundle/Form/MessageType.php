<?php

namespace MessageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use EmployeeBundle\Entity\Department;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class MessageType extends AbstractType
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
                '1' => 'Wichtig',
                '2' => 'Warnung',
                '3' => 'Information',
            ],
        ])
            ->add('message', TextareaType::class, [
              'attr' => ['class' => 'tinymce'],
                'mapped' => false,
                'required' => true,
            ])
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
            'data_class' => 'MessageBundle\Entity\Messages',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'Messagebundle_Message';
    }


}
