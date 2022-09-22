<?php

namespace VehicleLogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class VehicleLogCreateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('vehicleLogBeginTime', DateTimeType::class, [
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
            ->add('beginPosition', null, [
                'mapped'=>false,
                'required'   => true,
            ])
            ->add('beginPositionMileage', null, [
                'mapped'=>false,
                'required'   => true,
            ])
            /*->add('employee', null, [
                'required'   => true,
            ])*/
            ->add('reason', null, [
                'required'   => true,
            ])
            ->add('vehicleClean', null, [
                'attr' => [
                    'checked'   => true,
                ]
            ])
            ->add('comment')
            ->add('beginPositionLat', HiddenType::class, [
                'mapped'=>false,
            ])
            ->add('beginPositionLon', HiddenType::class, [
                'mapped'=>false,
            ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VehicleLogBundle\Entity\VehicleLog'
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
