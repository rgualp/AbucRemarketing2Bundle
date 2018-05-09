<?php
namespace MyCp\FrontEndBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class registerTourOperatorType extends AbstractType
{
    private $translate;

    function __construct($trans_entity, $data)
    {
        $this->translate = $trans_entity;
        $this->data=$data;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $array_countries=array();
        foreach($this->data['countries'] as $country)
        {
            $array_countries[$country->getCoId()]=$country->getCoName();
        }
        $array_roles=array($this->translate->trans('dashboard.operators'),$this->translate->trans('dashboard.admin'),$this->translate->trans('dashboard.accounting'));


        $builder
            ->add('user_user_name','text',array(
                'label'=>$this->translate->trans('FORMS_NAME'),
                'constraints'=>array(new NotBlank())
            ))
            ->add('user_last_name','text',array(
                'label'=>$this->translate->trans('FORMS_LASTNAME'),
                'constraints'=>array(new NotBlank())
            ))
            ->add('user_email','text',array(
                'label'=>$this->translate->trans('FORMS_EMAIL'),
                'constraints'=>array(new NotBlank(), new Email())
            ))
            ->add('user_password','repeated',array(
                'first_options' => array('label' => $this->translate->trans('FORMS_PASSWORD')),
                'second_options' => array('label' =>$this->translate->trans('FORMS_REPEAT')),
                'type' => 'password',
                'constraints'=>array(new NotBlank(),new Length(array('min'=>6)))
            ))
            ->add('user_country','choice',array(
            'choices'=>$array_countries,
            'empty_value' => '',
            'label'=>$this->translate->trans('COUNTRY'),
            'attr'=>array('class'=>'form-control user_country'),
            'constraints'=>array(new NotBlank())
            ))
            ->add('user_role','choice',array(
                'choices'=>$array_roles,
                'empty_value' => '',
                'label'=>$this->translate->trans('Role'),
                'attr'=>array('class'=>'form-control user_role'),
                'constraints'=>array(new NotBlank())
            ))
        ;
        ;
    }


    public function getName()
    {
        return 'mycp_frontendbundle_register_usertype';
    }
}
