<?php
namespace MyCp\frontEndBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\MinLength;
use Symfony\Component\Validator\Constraints\NotBlank;

class ownerContact extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('owner_full_name','text',array('label'=>'Nombre y apellidos del propietario:'))
            ->add('owner_own_name','text',array('label'=>'Nombre de la casa:'))
            ->add('owner_phone','text',array('label'=>'Telefono:'))
            ->add('owner_email','repeated',array(
            'first_name' => 'Email:',
            'second_name' => 'Repetir_email:',
            'type' => 'text',
        ))
            ->add('owner_province','text',array('label' => 'Provincia:'))
            ->add('owner_mun','text',array('label' => 'Municipio:'))
            ->add('owner_comment','textarea',array('label' => 'Comentarios:'));
        
    }

    public function getDefaultOptions(array $options)
    {
        $array['owner_full_name']= array(new NotBlank());
        $array['owner_own_name']= array(new NotBlank());
        $array['owner_phone']= array(new NotBlank());
        $array['owner_email']= array(new NotBlank(), new Email());
        $array['owner_province']= array(new NotBlank());
        $array['owner_mun']= array(new NotBlank());
        $collectionConstraint = new Collection($array);

        return array('validation_constraint' => $collectionConstraint);
    }

    public function getName()
    {
        return 'mycp_frontendbundle_contact_owner';
    }
}
