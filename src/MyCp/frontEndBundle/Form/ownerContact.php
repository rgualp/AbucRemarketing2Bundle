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
            ->add('owner_full_name','text',array('label'=>$this->translate->trans('FORMS_NAME_LASTNAME_OWNER'),'attr'=>array('class'=>'mycp_text_box mycp_text_box_margin')))
            ->add('owner_own_name','text',array('label'=>$this->translate->trans('FORMS_OWN_NAME'),'attr'=>array('class'=>'mycp_text_box mycp_text_box_margin')))
            ->add('owner_phone','text',array('label'=>$this->translate->trans('FORMS_PHONE'),'attr'=>array('class'=>'mycp_text_box mycp_text_box_margin')))
            ->add('owner_email','repeated',array(
            'first_name' => $this->translate->trans('FORMS_EMAIL'),
            'second_name' => $this->translate->trans('FORMS_REPEAT'),
            'type' => 'text'
        ))
            ->add('owner_province','text',array('label' => $this->translate->trans('FORMS_PROVINCE'),'attr'=>array('class'=>'mycp_text_box mycp_text_box_margin')))
            ->add('owner_mun','text',array('label' => $this->translate->trans('FORMS_MUNICIPALITY'),'attr'=>array('class'=>'mycp_text_box mycp_text_box_margin')))
            ->add('owner_comment','textarea',array('label' => $this->translate->trans('FORMS_COMMENTS'),'attr'=>array('class'=>'mycp_text_box mycp_text_box_margin input-block-level','style'=>'background-color:#FFFFFF')));
        
    }

    public function getDefaultOptions(array $options)
    {
        $array['owner_full_name']= array(new NotBlank());
        $array['owner_own_name']= array(new NotBlank());
        $array['owner_phone']= array(new NotBlank());
        $array['owner_email']= array(new NotBlank(), new Email());
        $array['owner_province']= array(new NotBlank());
        $array['owner_mun']= array(new NotBlank());
        $array['owner_comment']= array(new NotBlank());
        $collectionConstraint = new Collection($array);

        return array('validation_constraint' => $collectionConstraint);
    }

    public function getName()
    {
        return 'mycp_frontendbundle_contact_owner';
    }
}
