<?php

namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class overrideUserType extends AbstractType {

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
                ->add('override_by', 'choice', array('empty_value' => '', 'choices' => $users,
                    'label' => 'Usuario:',
                    'constraints' => array(new NotBlank())
                ))
            ->add('override_to', 'choice', array('empty_value' => '', 'choices' => $users,
                    'label' => 'Suplanta a:',
                    'constraints' => array(new NotBlank())
                ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\overrideuser'
        ));
    }

    public function getName() {
        return 'mycp_mycpbundle_overrideuser';
    }

}
