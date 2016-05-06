<?php

namespace MyCp\FrontEndBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class enableUserCasaType extends AbstractType {

    private $translate;

    function __construct($trans_entity) {
        $this->translate = $trans_entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('user_password', 'repeated', array(
                    'first_name' => $this->translate->trans('FORMS_PASSWORD'),
                    'second_name' => $this->translate->trans('FORMS_REPEAT'),
                    'first_options' => array('attr' =>array('class'=>'form-control')),
                    'second_options' => array('attr' =>array('class'=>'form-control')),
                    'type' => 'password',
//                    'attr' => array('class' => 'form-control'),
                    'constraints' => array(new NotBlank(), new Length(array('min' => 6)))
                ))
                ->add('user_user_name', 'text', array(
                    'label' => $this->translate->trans('FORMS_NAME'),
                    'attr' => array('class' => 'form-control'),
                    'constraints' => array(new NotBlank())
                ))
                ->add('user_last_name', 'text', array(
                    'label' => $this->translate->trans('FORMS_LASTNAME'),
                    'attr' => array('class' => 'form-control'),
                    'constraints' => array(new NotBlank())
                ))
                ->add('user_email', 'text', array(
                    'label' => $this->translate->trans('FORMS_EMAIL'),
                    'attr' => array('class' => 'form-control'),
                    'constraints' => array(new NotBlank(), new Email())
                ))
                ->add('user_phone', 'text', array(
                    'label' => $this->translate->trans('FORMS_PHONE'),
                    'attr' => array('class' => 'form-control'),
                    'constraints' => array(new NotBlank())
                ))
                ->add('user_address', 'textarea', array(
                    'label' => $this->translate->trans('ADDRESS'),
                    'attr' => array('class' => 'form-control'),
                    'constraints' => array(new NotBlank())
        ));
    }

    public function getName() {
        return 'mycp_frontendbundle_enabled_user_casatype';
    }

}
