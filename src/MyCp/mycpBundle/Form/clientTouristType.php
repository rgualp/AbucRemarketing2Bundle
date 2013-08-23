<?php
namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\MinLength;
use Symfony\Component\Validator\Constraints\NotBlank;

class clientTouristType extends AbstractType
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
            'label'=>'País:'));
        $builder->add('language',
            'choice',array('empty_value' => '','choices'=>$array_langs,
                'label'=>'Idioma:'));
        $builder->add('user_name','text',array('label'=>'Usuario:'));
        $builder->add('name','text',array('label'=>'Nombre:'));
        $builder->add('last_name','text',array('label'=>'Apellidos:'));
        $builder->add('email','text',array('label'=>'Email:'));
        $builder->add('phone','text',array('label'=>'Teléfono:'));
        $builder->add('address','text',array('label'=>'Dirección particular:'));
        $builder->add('city','text',array('label'=>'Ciudad:'));
        $builder->add('currency',
            'choice',array('empty_value' => '','choices'=>$array_currencies,
                'label'=>'Moneda:'));
        $builder->add('user_password','repeated',array(
        'first_name' => 'Clave:',
        'second_name' => 'Repetir_clave:',
        'type' => 'password',
    ));
    }

    public function getDefaultOptions(array $options)
    {

        $array=array();
        $array['photo']= array();
        $array['country']= array(new NotBlank());
        $array['name']= array(new NotBlank());
        $array['user_name']= array(new NotBlank());
        $array['last_name']= array(new NotBlank());
        $array['address']= array(new NotBlank());
        $array['email']= array(new Email(),new NotBlank());
        $array['phone']= array(new NotBlank());
        $array['address']= array(new NotBlank());
        $array['city']= array(new NotBlank());
        $array['language']= array(new NotBlank());
        $array['currency']= array(new NotBlank());
        if(isset($this->data['edit']) && $this->data['password']=='')
        {
            $array['user_password']= array();
        }
        else
        {
            $array['user_password']= array(new NotBlank(),new MinLength(6));
        }
        $collectionConstraint = new Collection($array);

        return array('validation_constraint' => $collectionConstraint);
    }

    public function getName()
    {
        return 'mycp_mycpbundle_client_touristtype';
    }

    public function setData($data)
    {
        $this->data=$data;
    }
}
