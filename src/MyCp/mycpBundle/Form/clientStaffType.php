<?php

namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\MinLength;
use Symfony\Component\Validator\Constraints\NotBlank;

class clientStaffType extends AbstractType
{
    private $data;

    public function __construct($data){
        $this->data=$data;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $array_countries=array();
        foreach($this->data['countries'] as $country)
        {
            $array_countries[$country->getCoId()]=$country->getCoName();
        }

        $builder
            ->add('user_name','text',array('label'=>'Usuario:'))
            ->add('user_user_name','text',array('label'=>'Nombre:'))
            ->add('user_last_name','text',array('label'=>'Apellidos:'))
            ->add('user_address','text',array('label'=>'Dirección:'))
            ->add('user_email','text',array('label'=>'Email:'))
            ->add('user_country',
                'choice',array('empty_value' => '','choices'=>$array_countries,
                    'label'=>'País:'))
            ->add('user_city','text',array('label'=>'Ciudad:'))
            ->add('user_phone','text',array('label'=>'Teléfono:'))
            ->add('user_photo','file',array('label'=>'Foto Staff:', 'attr'=>array('title'=>'Seleccionar fichero...','accept'=>'image/*')))
            ->add('user_password','repeated',array(
            'first_name' => 'Clave:',
            'second_name' => 'Repetir_clave:',
            'type' => 'password',
        ));
        ;
    }

    public function getDefaultOptions(array $options)
    {

        $array=array();

        $array['user_name']= array(new NotBlank());
        $array['user_user_name']= array(new NotBlank());
        $array['user_last_name']= array(new NotBlank());
        $array['user_address']= array(new NotBlank());
        $array['user_email']= array(new Email(),new NotBlank());
        $array['user_country']= array(new NotBlank());
        $array['user_city']= array(new NotBlank());
        $array['user_phone']= array(new NotBlank());
        $array['user_photo']= array();

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
        return 'mycp_mycpbundle_client_stafftype';
    }

    public function setData($data)
    {
        $this->data=$data;
    }
}
