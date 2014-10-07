<?php

namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class municipalityType extends AbstractType
{
    private $data;

    public function __construct($data){
        $this->data=$data;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $provinces=array();
        foreach($this->data['provinces'] as $prov)
        {
            $provinces[$prov->getProvId()]=$prov->getProvName();
        }

        $builder
            ->add('mun_name',null,array('label'=>'Nombre:', 'attr'=>array('class'=>'input-block-level')))
            ->add('mun_prov_id',
            'choice',array('empty_value' => '','choices'=>$provinces,
            'label'=>'Provincia:',
            'constraints'=>array(new NotBlank())
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\municipality'
        ));
    }

    public function getName()
    {
        return 'mycp_mycpbundle_municipalitytype';
    }
}
