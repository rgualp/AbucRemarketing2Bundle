<?php
namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class awardType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',null,array('label'=>'Nombre:','attr'=>array('class'=>'input-block-level')))
            ->add('ranking_value',null,array('label'=>'Valor ranking:','attr'=>array('class'=>'input-block-level')))
            ->add('icon_or_class_name',null,array('label'=>'Nombre de icono o clase CSS:','attr'=>array('class'=>'input-block-level')));
           // ->add('photo','file',array('label'=>'Ícono del premio:','mapped'=>false,
           // 'attr'=>array('title'=>"Seleccionar fichero...",'accept'=>'image/*')));

    }

    public function getName()
    {
        return 'mycp_mycpbundle_awardtype';
    }
}
