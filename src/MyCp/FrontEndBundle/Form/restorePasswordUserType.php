<?php
namespace MyCp\FrontEndBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\MinLength;
use Symfony\Component\Validator\Constraints\NotBlank;

class restorePasswordUserType extends AbstractType
{
    private $translate;

    function __construct($trans_entity)
    {
        $this->translate = $trans_entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user_email','text',array(
                'label'=>$this->translate->trans('FORMS_EMAIL'),
                'attr'=>array('class'=>'form-control'),
                'constraints'=>array(new NotBlank(), new Email())
            ))
        ;
    }

    public function getName()
    {
        return 'mycp_frontendbundle_restore_password_usertype';
    }
}
