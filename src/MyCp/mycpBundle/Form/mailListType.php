<?php

namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class mailListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $functions = array();
        $functions[0] = "";
        $mailFunctions = \MyCp\mycpBundle\Entity\mailList::getAllMailFunctions();

        foreach($mailFunctions as $mFunc)
            array_push($functions, $mFunc);

        $builder
            ->add('mail_list_name',null,array('label'=>'Nombre:', 'attr'=>array('class'=>'input-block-level')))
            ->add('mail_list_function','choice', array('choices'=>$functions,'label'=>'Función o comando','attr'=>array('class'=>'input-block-level')))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\mailList'
        ));
    }

    public function getName()
    {
        return 'mycp_mycpbundle_maillisttype';
    }
}
