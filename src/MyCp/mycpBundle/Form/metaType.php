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
            $keywords='';
            $description='';
            if(isset($options['data']['meta'][$cont]))
            {
                $keywords=$options['data']['meta'][$cont]->getMetaLangKeywords();
            }

            if(isset($options['data']['meta'][$cont]))
            {
                $description=$options['data']['meta'][$cont]->getMetaLangDescription();
            }

            $builder->add('keywords_'.$lang->getLangId(),'text',array('attr'=>array('value'=>$keywords,'class'=>'span12'),'label'=>'Keywords '.$lang->getLangName(), 'constraints'=>array(new NotBlank(), new Length(array('max'=>255)))));
            $builder->add('description_'.$lang->getLangId(),'textarea',array('data'=>$description,'attr'=>array('class'=>'span12'),'label'=>'Descripción '.$lang->getLangName(), 'constraints'=>array(new NotBlank(), new Length(array('max'=>255)))));
            $cont++;
        }

    }

    public function getName()
    {
        return 'mycp_mycpbundle_metatype';
    }
}