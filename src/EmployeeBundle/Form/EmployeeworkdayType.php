<?php

namespace EmployeeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
class EmployeeworkdayType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    
        $builder
        ->add('Zeit', DateTimeType::class, [
            'widget' => 'single_text',
            'format' => 'HH:mm',
            'mapped'=>false,
            'attr' => [
                'class' => 'form-control input-inline datetimepicker',
                'data-provide' => 'datepicker',
                'data-date-format' => 'HH:mm',
                'data-date-default-date' => date('Y-m-d H:i'),
            ],
            'html5' => false,
            ])
        
        ->add('tempemployeeId', HiddenType::class, [
            'mapped'=>false,
        ])
       ->add('Bemerkung', null, [
                    'mapped'=>false,
                    //'value'=> '',
                    'required'   => true,
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EmployeeBundle\Entity\Employeeworkday'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'employeebundle_employeeworkday';
    }


}
