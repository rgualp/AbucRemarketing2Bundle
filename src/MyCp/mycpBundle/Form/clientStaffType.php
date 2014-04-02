<?php

namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
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
            ->add('user_name','text',array(
                'label'=>'Usuario:',
                'constraints'=>array(new NotBlank())
            ))
            ->add('user_user_name','text',array(
                'label'=>'Nombre:',
                'constraints'=>array(new NotBlank())
            ))
            ->add('user_last_name','text',array(
                'label'=>'Apellidos:',
                'constraints'=>array(new NotBlank())
            ))
            ->add('user_address','text',array(
                'label'=>'Dirección:',
                'constraints'=>array(new NotBlank())
            ))
            ->add('user_email','text',array(
                'label'=>'Email:',
                'constraints'=>array(new NotBlank(), new Email())
            ))
            ->add('user_country',
                'choice',array('empty_value' => '','choices'=>$array_countries,
                'label'=>'País:',
                'constraints'=>array(new NotBlank())
                ))
            ->add('user_city','text',array(
                'label'=>'Ciudad:',
                'constraints'=>array(new NotBlank())
            ))
            ->add('user_phone','text',array(
                'label'=>'Teléfono:',
                'constraints'=>array(new NotBlank())
            ))
            ->add('user_photo','file',array('label'=>'Foto Staff:', 'attr'=>array('title'=>'Seleccionar fichero...','accept'=>'image/*'))
            );

        if(isset($this->data['edit']) && $this->data['password']=='')
        {
            $builder->add('user_password','repeated',array(
                'first_name' => 'Clave:',
                'second_name' => 'Repetir_clave:',
                'type' => 'password'));
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
        return 'mycp_mycpbundle_client_stafftype';
    }

    public function setData($data)
    {
        $this->data=$data;
    }
}
