<?php
namespace MyCp\frontEndBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\MinLength;
use Symfony\Component\Validator\Constraints\NotBlank;

class registerUserType extends AbstractType
{
    private $translate;
    
    function __construct($trans_entity)
    {
        $this->translate = $trans_entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user_user_name','text',array('label'=>$this->translate->trans('FORMS_NAME')))
            ->add('user_last_name','text',array('label'=>$this->translate->trans('FORMS_LASTNAME')))
            ->add('user_email','text',array('label'=>$this->translate->trans('FORMS_EMAIL')))
            ->add('user_password','repeated',array(
            'first_name' => $this->translate->trans('FORMS_PASSWORD'),
            'second_name' => $this->translate->trans('FORMS_REPEAT'),
            'type' => 'password',
        ));
        ;
    }

    public function getDefaultOptions(array $options)
    {
        $array['user_user_name']= array(new NotBlank());
        $array['user_last_name']= array(new NotBlank());
        $array['user_email']= array(new NotBlank(), new Email());
        $array['user_password']= array(new NotBlank(),new MinLength(6));
        $collectionConstraint = new Collection($array);

        return array('validation_constraint' => $collectionConstraint);
    }

    public function getName()
    {
        return 'mycp_frontendbundle_register_usertype';
    }
}
