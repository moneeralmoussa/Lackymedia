<?php

namespace VehicleLogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class VehicleLogFinalizeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('vehicleLogEndTime', DateTimeType::class, [
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
            ->add('endPosition', null, [
                'mapped'=>false,
                'required'   => true,
            ])
            ->add('endPositionMileage', null, [
                'mapped'=>false,
                'required'   => true,
            ])
            ->add('reason', null, [
                'required'   => true,
            ])
            ->add('comment')
            ->add('endPositionLat', HiddenType::class, [
                'mapped'=>false,
            ])
            ->add('endPositionLon', HiddenType::class, [
                'mapped'=>false,
            ]);

        /*$builder->get('vehicleLogBeginTime')
            ->addModelTransformer(new CallbackTransformer(
                function ($datetime) {
                    // transform the array to a string
                    return $datetime->format('Y-m-d H:i:s');
                },
                function ($string) {
                    // transform the string back to an array
                    return new \DateTime($string);
                }
            ))
        ;*/
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
