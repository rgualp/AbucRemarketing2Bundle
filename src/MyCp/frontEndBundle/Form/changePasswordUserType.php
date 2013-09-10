<?php
namespace MyCp\frontEndBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\MinLength;
use Symfony\Component\Validator\Constraints\NotBlank;

class changePasswordUserType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user_password','repeated',array(
            'first_name' => 'Clave:',
            'second_name' => 'Repetir_clave:',
            'type' => 'password',
        ));
        ;
    }

    public function getDefaultOptions(array $options)
    {
        $array['user_password']= array(new NotBlank(),new MinLength(6));
        $collectionConstraint = new Collection($array);

        return array('validation_constraint' => $collectionConstraint);
    }

    public function getName()
    {
        return 'mycp_frontendbundle_change_password_usertype';
    }
}
