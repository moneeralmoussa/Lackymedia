<?php

namespace VehicleLogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Doctrine\ORM\EntityRepository;

class VehicleReservationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('vehicle', null, [
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('v')
                        ->leftJoin('v.vehicletype', 'vt')
                        ->andWhere("vt.vehicletypetype='6'")
                        ->orderBy('v.name', 'ASC');
                }
            ])
            ->add('vehicleReservationBeginTime', DateTimeType::class, [
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
            ->add('beginPositionLat', HiddenType::class, [
                'mapped'=>false,
            ])
            ->add('beginPositionLon', HiddenType::class, [
                'mapped'=>false,
            ])
            ->add('vehicleReservationEndTime', DateTimeType::class, [
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
            ->add('endPositionLat', HiddenType::class, [
                'mapped'=>false,
            ])
            ->add('endPositionLon', HiddenType::class, [
                'mapped'=>false,
            ])
            ->add('employee', null, [
                'required'   => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->orderBy('e.name', 'ASC');
                }
            ])
            ->add('reason', null, [
                'required'   => true,
            ])
            ->add('comment');
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'VehicleLogBundle\Entity\VehicleReservation'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'vehiclelogbundle_vehiclereservation';
    }


}
