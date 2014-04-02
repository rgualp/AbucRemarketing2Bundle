<?php
namespace MyCp\frontEndBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\MinLength;
use Symfony\Component\Validator\Constraints\NotBlank;

class ownerContact extends AbstractType
{
    private $translate;
    
    function __construct($trans_entity)
    {
        $this->translate = $trans_entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('owner_full_name','text',array(
                'label'=>$this->translate->trans('FORMS_NAME_LASTNAME_OWNER'),
                'attr'=>array('class'=>'form-control'),
                'constraints'=>array(new NotBlank())
            ))
            ->add('owner_own_name','text',array(
                'label'=>$this->translate->trans('FORMS_OWN_NAME'),
                'attr'=>array('class'=>'form-control'),
                'constraints'=>array(new NotBlank()),
            ))
            ->add('owner_phone','text',array(
                'label'=>$this->translate->trans('FORMS_PHONE'),
                'attr'=>array('class'=>'form-control'),
                'constraints'=>array(new NotBlank()),
            ))
            ->add('owner_email','text',array(
                'label'=>$this->translate->trans('FORMS_EMAIL'),
                'attr'=>array('class'=>'form-control'),
                'constraints'=>array(new NotBlank(),new Email()),
            ))
            ->add('owner_province','text',array(
                'label' => $this->translate->trans('FORMS_PROVINCE'),
                'attr'=>array('class'=>'form-control'),
                'constraints'=>array(new NotBlank()),
            ))
            ->add('owner_mun','text',array(
                'label' => $this->translate->trans('FORMS_MUNICIPALITY'),
                'attr'=>array('class'=>'form-control'),
                'constraints'=>array(new NotBlank()),
            ))
            ->add('owner_comment','textarea',array(
                'label' => $this->translate->trans('FORMS_COMMENTS'),
                'attr'=>array('class'=>'form-control'),
                'constraints'=>array(new NotBlank()),
            ));
        
    }

    public function getName()
    {
        return 'mycp_frontendbundle_contact_owner';
    }
}
