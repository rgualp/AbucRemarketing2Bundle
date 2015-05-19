<?php

namespace MyCp\mycpBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class roleType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('role_name')
            ->add('role_parent')
            ->add('role_fixed')
            /*->add('permissions', 'collection',    [
                'type' => new rolePermissionType(),
                // 'allow_add' => true,
                'by_reference' => true,
                // 'allow_delete' => true,
                'label' => false
            ])*/
                ->add('permissions', 'entity', array(
                'class' => 'mycpBundle:permission',
                'query_builder' => function (EntityRepository $er) {
                 return $er->createQueryBuilder('p')
               ->orderBy('p.perm_description', 'ASC');
             //  ->groupBy('p.perm_category');
           },
                'expanded'=>true,
                'multiple'=>true
            ))
            ->add('cancel', 'reset')
            ->add('save', 'submit')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\role'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mycp_mycpbundle_role';
    }
}
