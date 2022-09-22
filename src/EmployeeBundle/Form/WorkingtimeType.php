<?php

namespace EmployeeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class WorkingtimeType extends AbstractType
{
    protected $_weekdays;
    public function __construct() {
        $this->_weekdays = array(
            1 => 'Montag',
            2 => 'Dienstag',
            3 => 'Mittwoch',
            4 => 'Donnerstag',
            5 => 'Freitag',
            6 => 'Samstag',
            7 => 'Sonntag',
        );
    }
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contract')
            ->add('dayOfWeek', ChoiceType::class, array(
                'choices' => $this->_weekdays,
                'choices_as_values' => false,
                'multiple' => false,
            ))
            /*->add('workBegin', DateTimeType::class, [
                'widget' => 'single_text',
                'format' => 'HH:mm',
                'attr' => [
                    'class' => 'form-control input-inline datetimepicker',
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'HH:mm',
                    'data-date-default-date' => date('Y-m-d H:i'),
                ],
                'html5' => false,
            ])
            ->add('workEnd', DateTimeType::class, [
                'widget' => 'single_text',
                'format' => 'HH:mm',
                'attr' => [
                    'class' => 'form-control input-inline datetimepicker',
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'HH:mm',
                    'data-date-default-date' => date('Y-m-d H:i'),
                ],
                'html5' => false,
            ])
            ->add('breakBegin', DateTimeType::class, [
                'widget' => 'single_text',
                'format' => 'HH:mm',
                'attr' => [
                    'class' => 'form-control input-inline datetimepicker',
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'HH:mm',
                    'data-date-default-date' => date('Y-m-d H:i'),
                ],
                'html5' => false,
            ])
            ->add('breakEnd', DateTimeType::class, [
                'widget' => 'single_text',
                'format' => 'HH:mm',
                'attr' => [
                    'class' => 'form-control input-inline datetimepicker',
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'HH:mm',
                    'data-date-default-date' => date('Y-m-d H:i'),
                ],
                'html5' => false,
            ])*/
            ->add('workBegin')->add('workEnd')->add('breakBegin')->add('breakEnd')
            ->add('overtimePremium')->add('overtimePremiumPassenger')->add('overtimePremiumIsBrutto')->add('specialProvision')->add('school')
            /*->add('schoolBegin', DateTimeType::class, [
                'widget' => 'single_text',
                'format' => 'HH:mm',
                'attr' => [
                    'class' => 'form-control input-inline datetimepicker',
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'HH:mm',
                    'data-date-default-date' => date('Y-m-d H:i'),
                ],
                'html5' => false,
            ])
            ->add('schoolEnd', DateTimeType::class, [
                'widget' => 'single_text',
                'format' => 'HH:mm',
                'attr' => [
                    'class' => 'form-control input-inline datetimepicker',
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'HH:mm',
                    'data-date-default-date' => date('Y-m-d H:i'),
                ],
                'html5' => false,
            ]);*/
            ->add('schoolBegin')->add('schoolEnd');
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EmployeeBundle\Entity\Workingtime'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'employeebundle_workingtime';
    }


}
