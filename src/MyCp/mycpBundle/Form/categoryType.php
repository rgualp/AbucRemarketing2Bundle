<?php
namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\MinLength;
use Symfony\Component\Validator\Constraints\NotBlank;

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
                $builder->add('lang'.$language->getLangId(), 'text',array(
                    'attr' => array('class'=>'span6','value'=>$array_langs_text[$a]->getAlbumCatDescription()),
                    'data'=>'','label'=>'Nombre en '.$language->getLangName().':'));
                $a++;
            }
        }
        else if(isset($this->data['faq_cat_lang']))
        {
            $a=0;
            $array_langs_text=$this->data['faq_cat_lang'];
            foreach($this->data['languages'] as $language)
            {
                $value='';
                if(isset($array_langs_text[$a]))
                {
                   $value= $array_langs_text[$a]->getFaqCatDescription();
                }
                $builder->add('lang'.$language->getLangId(), 'text',array(
                    'attr' => array('class'=>'span6','value'=>$value),
                    'data'=>'','label'=>'Nombre en '.$language->getLangName().':'));
                $a++;
            }
        }
        else if(isset($this->data['des_cat_lang']))
        {
            $a=0;
            $array_langs_text=$this->data['des_cat_lang'];
            foreach($this->data['languages'] as $language)
            {
                $builder->add('lang'.$language->getLangId(), 'text',array(
                    'attr' => array('class'=>'span6','value'=>$array_langs_text[$a]->getDesCatName()),
                    'data'=>'','label'=>'Nombre en '.$language->getLangName().':'));
                $a++;
            }
        }
        else
        {
            foreach($this->data['languages'] as $language)
            {
                $builder->add('lang'.$language->getLangId(), 'text',array(
                    'attr' => array('class'=>'span6'),
                    'data'=>'','label'=>'Nombre en '.$language->getLangName().':'));

            }
        }

        if(isset($this->data['des_photo']))
        {
            $builder->add('photo','file',array('label'=>'Ícono:','mapped'=>false,
                'attr'=>array('title'=>"Seleccionar fichero...",'accept'=>'image/*')));
        }
    }

    public function getDefaultOptions(array $options)
    {

        $array=array();
        foreach($this->data['languages'] as $language)
        {
           $array['lang'.$language->getLangId()]= array(new MinLength(3),new NotBlank());
        }
        $collectionConstraint = new Collection($array);

        return array('validation_constraint' => $collectionConstraint);
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
