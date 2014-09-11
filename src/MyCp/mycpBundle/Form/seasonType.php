<?php

namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class seasonType extends AbstractType
{
    private $data;

    public function __construct($data){
        $this->data=$data;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $destinations=array();
        foreach($this->data['destinations'] as $des)
        {
            $destinations[$des->getDesId()]=$des->getDesName();
        }

        $builder
            ->add('season_type',
            'choice',array('empty_value' => '','choices'=>$this->data['season_types'],
            'label'=>'Tipo:',
            'attr' => array('class' => 'dd_season_types'),
            'constraints'=>array(new NotBlank())
            ))    
            ->add('season_startdate',null,array('widget'=>'single_text','format'=>'dd/MM/yyyy','label'=>'Inicio:', 'attr'=>array('class'=>'input-block-level datepicker-from')))
            ->add('season_enddate',null,array('widget'=>'single_text','format'=>'dd/MM/yyyy','label'=>'Fin:', 'attr'=>array('class'=>'input-block-level datepicker-to')))
            ->add('season_destination',
            'choice',array('empty_value' => '','choices'=>$destinations,
            'attr' => array('class' => 'dd_season_destination','disabled' => 'disabled'),
            'label'=>'Destino:'
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\season'
        ));
    }

    public function getName()
    {
        return 'mycp_mycpbundle_seasontype';
    }
}
