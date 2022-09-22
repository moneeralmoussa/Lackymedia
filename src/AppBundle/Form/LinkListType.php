<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
class LinkListType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            
            ->add('name', null, [
                'mapped'=>true,
                'required'   => true,
            ])
            ->add('Groupname', ChoiceType::class, [
                'choices' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    'Personal' => 'Personal',
                    'Werkstatt' => 'Werkstatt',
                    'Abrechnung' => 'Abrechnung',
                    'Buchhaltung' => 'Buchhaltung',
                    '' => 'Bereitschaft',
                    'Geschäftsleitung' => 'Geschäftsleitung',
                 ],
            ])
            ->add('name', null, [
                'mapped'=>true,
                'required'   => true,
            ])
            ->add('symbol', ChoiceType::class, [
                'choices' => [
                    '1' => 'Website',
                    '2' => 'Handy',
                    '3' => 'WhatsApp',
                ],
            ])
            ->add('link', null, [
                    'mapped'=>true,
                    'required'   => true,
                ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\LinkList'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'vehiclelogbundle_vehiclelog';
    }


}
