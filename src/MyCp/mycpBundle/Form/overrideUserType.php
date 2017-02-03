<?php

namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Doctrine\ORM\EntityRepository;


class overrideUserType extends AbstractType {


    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder

            ->add('override_by','text',array(
                    'label'=>'Usuario que suplanta:'
                ))
            ->add('override_to','text',array(
                    'label'=>'Usuario a suplantado:'
                ))
            ->add('reason','textarea',array(
                    'label'=>'Motivos:',
                    'attr'=>array('class'=>'textarea', "style" => "width: 83%")
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
