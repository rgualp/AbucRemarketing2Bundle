<?php

namespace MyCp\PartnerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class paContactType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('email', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('phone', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('mobile', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\PartnerBundle\Entity\paContact'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'partner_agency';
    }
}
