<?php
namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\MinLength;
use Symfony\Component\Validator\Constraints\NotBlank;

class categoryType extends AbstractType
{
    private $data;

    public function __construct($data){
        $this->data=$data;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        foreach($this->data['languages'] as $language)
        {
            $builder->add('lang'.$language->getLangId(), 'text',array(
                'attr' => array('class'=>'span6'),
                'data'=>'','label'=>'Nombre en '.$language->getLangName().':'));
        }
    }

    public function getDefaultOptions(array $options)
    {

        $array=array();
        foreach($this->data['languages'] as $language)
        {
           $array['lang'.$language->getLangId()]= array(new MinLength(5),new NotBlank());
        }
        $collectionConstraint = new Collection($array);

        return array('validation_constraint' => $collectionConstraint);
    }

    public function getName()
    {
        return 'mycp_mycpbundle_categorytype';
    }

    public function setData($data)
    {
        $this->data=$data;
    }
}
