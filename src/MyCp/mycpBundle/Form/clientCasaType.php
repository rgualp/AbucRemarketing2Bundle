<?php

namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class clientCasaType extends AbstractType {

    private $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $array_ownerships = array();
        foreach ($this->data['ownerships'] as $ownership) {
            $array_ownerships[$ownership->getOwnId()] = $ownership->getOwnMcpCode() . " - ". $ownership->getOwnName();
        }
        $builder->add('photo', 'file', array('label' => 'Fotografía:',
            'attr' => array('title' => "Seleccionar fichero...", 'accept' => 'image/*',)));
        if (isset($this->data['edit']))
            $builder->add('ownership', 'choice', array('empty_value' => '', 'choices' => $array_ownerships,
                'label' => 'Casa:', 'attr' => array('class' => 'input-block-level ownership', 'disabled' => "disabled")));
        else
            $builder->add('ownership', 'choice', array('empty_value' => '', 'choices' => $array_ownerships,
                'constraints' => array(new NotBlank()),
                'label' => 'Casa:', 'attr' => array('class' => 'input-block-level ownership')));
        $builder->add('user_name', 'text', array(
            'label' => 'Usuario:',
            'attr' => array('class' => 'login readonly', 'readonly' => "readonly"),
            'constraints' => array(new NotBlank())));
        $builder->add('name', 'text', array('label' => 'Nombre:',
            'attr' => array('class' => 'name'),
            'constraints' => array(new NotBlank()),
        ));
        $builder->add('last_name', 'text', array('label' => 'Apellidos:', 'attr' => array('class' => 'last_name'),
            'constraints' => array(new NotBlank())
        ));
        $builder->add('phone', 'text', array('label' => 'Teléfono:', 'attr' => array('class' => 'phone')));
        $builder->add('email', 'text', array('label' => 'Email:', 'attr' => array('class' => 'email'),
            'constraints' => array(new NotBlank(), new Email())));
        $builder->add('address', 'text', array('label' => 'Dirección particular:', 'attr' => array('class' => 'address readonly'),
            'constraints' => array(new NotBlank())));
        $builder->add('user_enabled', 'checkbox', array('label' => 'Usuario activo:', 'attr' => array('class' => 'user_enabled', 'disabled' => 'disabled')));
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

    public function getName() {
        return 'mycp_mycpbundle_client_casatype';
    }

    public function setData($data) {
        $this->data = $data;
    }

}
