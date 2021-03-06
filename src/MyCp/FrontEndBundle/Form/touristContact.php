<?php
namespace MyCp\FrontEndBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\MinLength;
use Symfony\Component\Validator\Constraints\NotBlank;

class touristContact extends AbstractType
{
    private $translate;

    function __construct($trans_entity)
    {
        $this->translate = $trans_entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tourist_name','text',array(
                'label'=>$this->translate->trans('FORMS_NAME'),
                'attr'=>array('class'=>'form-control'),
                'constraints'=>array(new NotBlank())
            ))
            ->add('tourist_last_name','text',array(
                'label'=>$this->translate->trans('FORMS_LASTNAME'),
                'attr'=>array('class'=>'form-control'),
                'constraints'=>array(new NotBlank())
            ))
            ->add('tourist_email','text',array(
                'label'=>$this->translate->trans('FORMS_EMAIL'),
                'attr'=>array('class'=>'form-control'),
                'constraints'=>array(new NotBlank(), new Email())
            ))
            ->add('tourist_phone','text',array(
                'label' => $this->translate->trans('FORMS_PHONE'),
                'attr'=>array('class'=>'form-control'),
                'constraints'=>array(new NotBlank())
            ))
            ->add('tourist_comment','textarea',array(
                'label' => $this->translate->trans('FORMS_COMMENTS'),
                'attr'=>array('class'=>'form-control input-block-level'),
                'constraints'=>array(new NotBlank())
            ));
        ;
    }

    public function getName()
    {
        return 'mycp_frontendbundle_contact_tourist';
    }
}
