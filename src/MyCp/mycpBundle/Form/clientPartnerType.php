<?php
namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class clientPartnerType extends AbstractType
{
    private $data;

    public function __construct($data){
        $this->data=$data;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $array_countries=array();
        $array_currencies=array();
        $array_langs=array();
        foreach($this->data['countries'] as $country)
        {
            $array_countries[$country->getCoId()]=$country->getCoName();
        }
        foreach($this->data['currencies'] as $currency)
        {
            $array_currencies[$currency->getCurrId()]=$currency->getCurrName();
        }
        foreach($this->data['languages'] as $language)
        {
            $lang=$language->getLangName();
            $lang[0]=strtoupper($lang[0]);
            $array_langs[$language->getLangId()]=$lang;
        }
        $builder->add('photo','file',array('label'=>'Fotografía:',
            'attr'=>array('title'=>"Seleccionar fichero...",'accept'=>'image/*')));
        $builder->add('country',
            'choice',array('empty_value' => '','choices'=>$array_countries,
            'constraints'=>array(new NotBlank()),
            'label'=>'País:','attr'=>array('class'=>'input-block-level')));
        $builder->add('language',
            'choice',array('empty_value' => '','choices'=>$array_langs,
            'constraints'=>array(new NotBlank()),
            'label'=>'Idioma:','attr'=>array('class'=>'input-block-level')));
        $builder->add('currency',
            'choice',array('empty_value' => '','choices'=>$array_currencies,
            'constraints'=>array(new NotBlank()),
            'label'=>'Moneda:','attr'=>array('class'=>'input-block-level ownership')));
        $builder->add('user_name','text',array(
            'label'=>'Usuario:',
            'constraints'=>array(new NotBlank())));
        $builder->add('company_code','text',array(
            'label'=>'Cod. Empresa:',
            'constraints'=>array(new NotBlank())
        ));
        $builder->add('company_name','text',array(
            'label'=>'Nombre de la empresa:',
            'constraints'=>array(new NotBlank())
        ));
        $builder->add('email','text',array(
            'label'=>'Email:',
            'constraints'=>array(new NotBlank(), new Email()),
        ));
        $builder->add('phone','text',array(
            'label'=>'Teléfono:',
            'constraints'=>array(new NotBlank())
        ));
        $builder->add('address','text',array(
            'label'=>'Dirección:',
            'constraints'=>array(new NotBlank())
        ));
        $builder->add('city','text',array(
            'label'=>'Ciudad:',
            'constraints'=>array(new NotBlank())
        ));
        $builder->add('contact_person','text',array(
            'label'=>'Persona de contacto:',
            'constraints'=>array(new NotBlank())
        ));

        if(isset($this->data['edit']) && $this->data['password']=='')
        {
            $builder->add('user_password','repeated',array(
                'first_name' => 'Clave:',
                'second_name' => 'Repetir_clave:',
                'type' => 'password',
            ));
        }
        else
        {
            $builder->add('user_password','repeated',array(
                'first_name' => 'Clave:',
                'second_name' => 'Repetir_clave:',
                'type' => 'password',
                'constraints'=>array(new NotBlank(),new Length(array('min'=>6)))
            ));
        }


    }

    public function getName()
    {
        return 'mycp_mycpbundle_client_partnertype';
    }

    public function setData($data)
    {
        $this->data=$data;
    }
}
