<?php

namespace AbsenceBundle\Form;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AbsenceType extends AbstractType
{
    protected $auth;

    public function __construct(AuthorizationCheckerInterface $auth)
    {
        $this->auth = $auth;
    }

    /**
     * {@inheritdoc}
     */

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $builder->getData();
        $delete_date=(new \DateTime())->format('Y-m-d H:i:s');
        if (is_null($entity->getEmployee())) {
            $builder->add('employee', null, [
            'query_builder' => function (EntityRepository $repository) {
                return $repository->createQueryBuilder('u')
                              ->where("u.deleted_at IS NULL  OR u.deleted_at >= '".$delete_date."'")
                    ->orderBy("u.name, u.prename");
            },]);
        }

        $builder
        ->add('fromDate', DateTimeType::class, [
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
        ->add('day', TextType::class, array(
            'attr' => array(
                'readonly' => true,
            ),
            'data' => '1',
        ))
        ->add('reason')
        ->add('note', TextareaType::class, array('required' => false))
        ;

        if ($this->auth->isGranted('ROLE_ADMIN') || $this->auth->isGranted('ROLE_HOLIDAY') || $this->auth->isGranted('ROLE_AZUBI_PERSONAL') || $this->auth->isGranted('ROLE_DIALOG_PERSONAL')) {
            $builder->add('status')
            ->add('sendInfo', CheckboxType::class, array('mapped' => false ,'required' => false, 'attr' => ['checked'=> true] ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AbsenceBundle\Entity\Absence'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'absencebundle_absence';
    }
}
