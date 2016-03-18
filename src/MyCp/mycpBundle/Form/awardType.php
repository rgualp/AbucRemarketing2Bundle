<?php
namespace MyCp\mycpBundle\Form;

use MyCp\mycpBundle\Helpers\FormMode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class awardType extends AbstractType
{
    private $formModeInsert;

    public function __construct($formModeInsert)
    {
        $this->formModeInsert = $formModeInsert;

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',null,array('label'=>'Nombre:','attr'=>array('class'=>'input-block-level')))
            ->add('ranking_value',null,array('label'=>'Valor ranking:','attr'=>array('class'=>'input-block-level')));

        if($this->formModeInsert) {
            $builder
                ->add('icon_or_class_name', 'file', array('label' => 'Ícono del premio:',
                    'data_class' => null, 'attr' => array('title' => "Seleccionar fichero...", 'accept' => 'image/*')))
                ->add('second_icon_or_class_name', 'file', array('label' => 'Ícono del premio (pequeño):',
                    'data_class' => null, 'attr' => array('title' => "Seleccionar fichero...", 'accept' => 'image/*')));
        }
        else{

            $builder
                ->add('icon_or_class_name', 'file', array('label' => 'Ícono del premio:',
                    'data_class' => null, "required" => false, 'attr' => array('title' => "Seleccionar fichero...", 'accept' => 'image/*')))
                ->add('second_icon_or_class_name', 'file', array('label' => 'Ícono del premio (pequeño):',
                    'data_class' => null, "required" => false, 'attr' => array('title' => "Seleccionar fichero...", 'accept' => 'image/*')));
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\award'
        ));
    }

    public function getName()
    {
        return 'mycp_mycpbundle_awardtype';
    }
}
