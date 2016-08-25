<?php

namespace MyCp\PartnerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class paTravelAgencyType extends AbstractType
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
            ->add('address', 'textarea', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('phone', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('contacts', 'collection', array(
                'required' => true,
                'type' => new paContactType(),
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
                /** @Ignore */ 'label' => false,
                'attr' => [
                    'class' => 'col-md-6'
                ]
            ))

        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\PartnerBundle\Entity\paTravelAgency'
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
