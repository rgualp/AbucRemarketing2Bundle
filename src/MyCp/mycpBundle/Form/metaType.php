<?php

namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class metaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $langs=$options['data']['langs'];
        $cont=0;
        foreach($langs as $lang)
        {
            $keywords=$options['data']['meta'];
            $builder->add('keywords_'.$lang->getLangId(),'text',array('attr'=>array('value'=>$options['data']['meta'][$cont]->getMetaLangKeywords(),'class'=>'span12'),'label'=>'Keywords '.$lang->getLangName(), 'constraints'=>array(new NotBlank(), new Length(array('max'=>255)))));
            $builder->add('description_'.$lang->getLangId(),'textarea',array('data'=>$options['data']['meta'][$cont]->getMetaLangDescription(),'attr'=>array('class'=>'span12'),'label'=>'Descripción '.$lang->getLangName(), 'constraints'=>array(new NotBlank(), new Length(array('max'=>255)))));
            $cont++;
        }

    }

    public function getName()
    {
        return 'mycp_mycpbundle_metatype';
    }
}