<?php

namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class mailListUserType extends AbstractType {

    private $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $users = array();
        foreach ($this->data['users'] as $user) {
            $users[$user->getUserId()] = $user->getUserCompleteName().' - '.$user->getUserEmail();
        }

        $builder
                ->add('mail_list_user', 'choice', array('empty_value' => '', 'choices' => $users,
                    'label' => 'Usuario:',
                    'constraints' => array(new NotBlank())
                ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\mailListUser'
        ));
    }

    public function getName() {
        return 'mycp_mycpbundle_maillistusertype';
    }

}
