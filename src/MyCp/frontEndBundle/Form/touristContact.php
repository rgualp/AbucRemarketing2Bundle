<?php
namespace MyCp\frontEndBundle\Form;

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
            ->add('tourist_name','text',array('label'=>$this->translate->trans('FORMS_NAME')))
            ->add('tourist_last_name','text',array('label'=>$this->translate->trans('FORMS_LASTNAME')))
            ->add('tourist_email','text',array('label'=>$this->translate->trans('FORMS_EMAIL')))
            ->add('tourist_phone','text',array('label' => $this->translate->trans('FORMS_PHONE')))
            ->add('tourist_comment','textarea',array('label' => $this->translate->trans('FORMS_COMMENTS')));
        ;
    }

    public function getDefaultOptions(array $options)
    {
        $array['tourist_name']= array(new NotBlank());
        $array['tourist_last_name']= array(new NotBlank());
        $array['tourist_email']= array(new NotBlank(), new Email());
        $array['tourist_phone']= array(new NotBlank());;
        $array['tourist_comment']= array(new NotBlank());
        $collectionConstraint = new Collection($array);

        return array('validation_constraint' => $collectionConstraint);
    }

    public function getName()
    {
        return 'mycp_frontendbundle_contact_tourist';
    }
}
