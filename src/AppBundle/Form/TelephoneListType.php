<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
class TelephoneListType extends AbstractType
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
                    'Sattelzug - Dispo' => 'Sattelzug - Dispo',
                    'Kransattel - Dispo' => 'Kransattel - Dispo',
                    'Motorwagen - Dispo' => 'Motorwagen - Dispo',
                    'Planen - Dispo' => 'Planen - Dispo',
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
                    '1' => 'Phone',
                    '2' => 'Handy',
                    '3' => 'WhatsApp',
                ],
            ])
            ->add('telephone', null, [
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
            'data_class' => 'AppBundle\Entity\TelephoneList'
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
