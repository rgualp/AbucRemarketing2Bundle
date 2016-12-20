<?php
namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class categoryType extends AbstractType
{
    private $data;

    public function __construct($data){
        $this->data=$data;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if(isset($this->data['album_cat_lang']))
        {
            $a=0;
            $array_langs_text=$this->data['album_cat_lang'];
            foreach($this->data['languages'] as $language)
            {
                $value=(isset($array_langs_text[$a])) ? $array_langs_text[$a]->getAlbumCatDescription() : '';

                $builder->add('lang'.$language->getLangId(), 'text',array(
                    'attr' => array('class'=>'span6','value'=> $value),
                    'data'=>'','label'=>'Nombre en '.$language->getLangName().':',
                    'constraints'=>array(new NotBlank(), new Length(array('max'=>255,'min'=>3)))
                ));


                $a++;
            }
        }
        else if(isset($this->data['faq_cat_lang']))
        {
            $a=0;
            $array_langs_text=$this->data['faq_cat_lang'];
            foreach($this->data['languages'] as $language)
            {
                $value=(isset($array_langs_text[$a])) ? $array_langs_text[$a]->getFaqCatDescription() : '';

                $builder->add('lang'.$language->getLangId(), 'text',array(
                    'attr' => array('class'=>'span6','value'=>$value),
                    'data'=>'','label'=>'Nombre en '.$language->getLangName().':',
                    'constraints'=>array(new NotBlank(), new Length(array('max'=>255,'min'=>3)))
                ));
                $a++;
            }
        }
        else if(isset($this->data['des_cat_lang']))
        {
            $a=0;
            $array_langs_text=$this->data['des_cat_lang'];
            foreach($this->data['languages'] as $language)
            {
                $value=(isset($array_langs_text[$a])) ? $array_langs_text[$a]->getDesCatName() : '';

                $builder->add('lang'.$language->getLangId(), 'text',array(
                    'attr' => array('class'=>'span6','value'=> $value),
                    'data'=>'','label'=>'Nombre en '.$language->getLangName().':',
                    'constraints'=>array(new NotBlank(), new Length(array('max'=>255,'min'=>3)))
                ));
                $a++;
            }
        }
        else
        {
            foreach($this->data['languages'] as $language)
            {
                $builder->add('lang'.$language->getLangId(), 'text',array(
                    'attr' => array('class'=>'span6'),
                    'data'=>'','label'=>'Nombre en '.$language->getLangName().':',
                    'constraints'=>array(new NotBlank(), new Length(array('max'=>255,'min'=>3)))
                ));

            }
        }

        if(isset($this->data['des_photo']))
        {
            $builder->add('photo','file',array('label'=>'Ícono atracción (Mapa Cuba):','mapped'=>false,
                'attr'=>array('title'=>"Seleccionar fichero...",'accept'=>'image/*')));
            $builder->add('photo_atraction','file',array('label'=>'Ícono atracción (Mapa provincia):','mapped'=>false,
                'attr'=>array('title'=>"Seleccionar fichero...",'accept'=>'image/*')));
        }
    }

    public function getName()
    {
        return 'mycp_mycpbundle_categorytype';
    }

    public function setData($data)
    {
        $this->data=$data;
    }
}
