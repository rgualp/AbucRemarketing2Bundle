<?php

namespace MyCp\PartnerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\NotBlank;

class paTravelAgencyType extends AbstractType
{
    private $translate;
    private $data;

    function __construct($trans_entity, $data)
    {
        $this->translate = $trans_entity;
        $this->data=$data;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $array_countries=array();
        if(isset($this->data['countries'])){
            foreach($this->data['countries'] as $country)
            {
                $array_countries[$country->getCoId()]=$country->getCoName();
            }
        }

        $builder
            ->add('name', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('email', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('address', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('phone', 'text', array('required' => true, 'attr' => array('class' => 'form-control')))
            ->add('phoneAux', 'text', array('required' => false, 'attr' => array('class' => 'form-control')))
            ->add('country','choice',array(
                'choices'=>$array_countries,
                'empty_value' => '',
                'attr'=>array('class'=>'form-control user_country'),
                'constraints'=>array(new NotBlank())
            ))
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
