<?php
namespace MyCp\FrontEndBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class changePasswordUserType extends AbstractType
{
     private $translate;

    function __construct($trans_entity)
    {
        $this->translate = $trans_entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user_password','repeated',array(
            'first_name' => $this->translate->trans('FORMS_PASSWORD'),
            'second_name' => $this->translate->trans('FORMS_REPEAT'),
            'type' => 'password',
            'constraints'=>array(new NotBlank(), new Length(array('min'=>6))),
        ));
        ;
    }

    public function getName()
    {
        return 'mycp_frontendbundle_change_password_usertype';
    }
}
