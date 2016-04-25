<?php

namespace MyCp\mycpBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class userStaffManagerDestinationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', 'entity', array(
                'class' => 'MyCp\mycpBundle\Entity\user',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')->orderBy('u.user_name', 'ASC');
                },
                'property' => 'user_name',
                'required' => true,
                'multiple' => false
            ))
            ->add('destinations', 'entity', array(
                'class' => 'MyCp\mycpBundle\Entity\destination',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('d')->orderBy('d.des_name', 'ASC');
                },
                'property' => 'des_name',
                'required' => true,
                'multiple' => true
            ))
        ;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'mycp_mycpbundle_userStaffManagerDestination';
    }
}
