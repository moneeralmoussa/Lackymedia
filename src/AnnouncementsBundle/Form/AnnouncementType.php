<?php

namespace AnnouncementsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class AnnouncementType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('title')
        ->add('text', TextareaType::class, [
          'required'   => true,
          'attr' => [
            'rows' => '6'
            ]
          ])
        ->add('expireAt', DateTimeType::class, [
            'widget' => 'single_text',
            'required'   => true,
            'attr' => [
                'class' => 'form-control input-inline datetimepicker',
            ],
            'html5' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AnnouncementsBundle\Entity\Announcement'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'announcementsbundle_announcement';
    }


}
