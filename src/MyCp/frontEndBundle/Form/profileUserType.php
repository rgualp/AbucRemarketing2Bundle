<?php
namespace MyCp\frontEndBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\MinLength;
use Symfony\Component\Validator\Constraints\NotBlank;

class profileUserType extends AbstractType
{
    private $translate;
    private $data;
    
    function __construct($trans_entity, $data)
    {
        $this->translate = $trans_entity;
        $this->data=$data;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $array_countries=array();
        foreach($this->data['countries'] as $country)
        {
            $array_countries[$country->getCoId()]=$country->getCoName();
        }
        
        $array_gender = array();
        $array_gender[0] = $this->translate->trans('MALE');
        $array_gender[1] = $this->translate->trans('FEMALE');
        
        $array_currencies=array();
        foreach($this->data['currencies'] as $currency)
        {
            $array_currencies[$currency->getCurrId()]=$currency->getCurrName();
        }
        
        $array_languages=array();
        foreach($this->data['languages'] as $lang)
        {
            $array_languages[$lang->getLangId()]=$lang->getLangName();
        }
        
        
        $builder
            //->add('user_photo','file',array('label'=>' ', 'attr'=>array('title'=>$this->translate->trans('SELECT_PHOTOGRAPHY'),'accept'=>'image/*')))
            ->add('user_user_name','text',array('label'=>$this->translate->trans('FORMS_NAME'),'attr'=>array('class'=>'form-control')))
            ->add('user_last_name','text',array('label'=>$this->translate->trans('FORMS_LASTNAME'),'attr'=>array('class'=>'form-control')))
            ->add('user_gender','choice',array('choices'=>$array_gender,'empty_value' => '','label'=>$this->translate->trans('GENDER'),'attr'=>array('class'=>'form-control user_gender'))) 
            ->add('user_email','text',array('label'=>$this->translate->trans('FORMS_EMAIL'),'attr'=>array('class'=>'form-control')))
            ->add('user_phone','text',array('label'=>$this->translate->trans('FORMS_PHONE'),'attr'=>array('class'=>'form-control')))
            ->add('user_cell','text',array('label'=>$this->translate->trans('CELL_NUMBER'),'attr'=>array('class'=>'form-control'))) 
            ->add('user_address','textarea',array('label'=>$this->translate->trans('ADDRESS'),'attr'=>array('class'=>'form-control')))
            ->add('user_city','text',array('label'=>$this->translate->trans('CITY_TAB_DESCRIPTION'),'attr'=>array('class'=>'form-control')))
            ->add('user_country','choice',array('choices'=>$array_countries,'empty_value' => '','label'=>$this->translate->trans('COUNTRY'),'attr'=>array('class'=>'form-control user_country'))) 
            ->add('user_zip_code','text',array('label'=>$this->translate->trans('ZIPCODE'),'attr'=>array('class'=>'form-control')))
            ->add('user_currency','choice',array('choices'=>$array_currencies,'empty_value' => '','label'=>$this->translate->trans('CURRENCY'),'attr'=>array('class'=>'form-control user_currency')))
            ->add('user_lang','choice',array('choices'=>$array_languages,'empty_value' => '','label'=>$this->translate->trans('LANGUAGE'),'attr'=>array('class'=>'form-control user_lang')))
            ->add('user_newsletter','checkbox',array('label'=>$this->translate->trans('NEWSLETTER_USER_REGISTRATION'),'attr'=>array('class'=>'form-control')))
            ;
    }

    public function getDefaultOptions(array $options)
    {
       // $array['user_photo']= array();
        $array['user_user_name']= array(new NotBlank());
        $array['user_last_name']= array(new NotBlank());
        $array['user_email']= array(new NotBlank(), new Email());
        $array['user_phone']= array(new NotBlank());
        $array['user_cell']= array(new NotBlank());
        $array['user_country']= array(new NotBlank());
        $array['user_zip_code']= array(new NotBlank());
        $array['user_address']= array(new NotBlank());
        $array['user_gender']= array(new NotBlank());
        $array['user_city']= array(new NotBlank());
        $array['user_currency']= array(new NotBlank());
        $array['user_lang']= array(new NotBlank());
        $array['user_newsletter']= array();
        $collectionConstraint = new Collection($array);

        return array('validation_constraint' => $collectionConstraint);
    }

    public function getName()
    {
        return 'mycp_frontendbundle_profile_usertype';
    }
}
