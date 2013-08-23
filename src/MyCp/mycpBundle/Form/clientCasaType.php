<?php
namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\MinLength;
use Symfony\Component\Validator\Constraints\NotBlank;

class clientCasaType extends AbstractType
{
    private $data;

    public function __construct($data){
        $this->data=$data;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $array_ownerships=array();
        foreach($this->data['ownerships'] as $ownership)
        {
            $array_ownerships[$ownership->getOwnId()]=$ownership->getOwnName();
        }
        $builder->add('photo','file',array('label'=>'Fotografía:',
            'attr'=>array('title'=>"Seleccionar fichero...",'accept'=>'image/*')));
        $builder->add('ownership',
            'choice',array('empty_value' => '','choices'=>$array_ownerships,
            'label'=>'Casa:','attr'=>array('class'=>'input-block-level ownership')));
        $builder->add('user_name','text',array('label'=>'Usuario:'));
        $builder->add('name','text',array('label'=>'Nombre:','attr'=>array('class'=>'name readonly')));
        $builder->add('last_name','text',array('label'=>'Apellidos:','attr'=>array('class'=>'last_name readonly')));
        $builder->add('email','text',array('label'=>'Email:','attr'=>array('class'=>'email readonly')));
        $builder->add('address','text',array('label'=>'Dirección particular:','attr'=>array('class'=>'address readonly')));
        $builder->add('user_password','repeated',array(
        'first_name' => 'Clave:',
        'second_name' => 'Repetir_clave:',
        'type' => 'password',
    ));
    }

    public function getDefaultOptions(array $options)
    {

        $array=array();
        $array['photo']= array();
        $array['ownership']= array(new NotBlank());
        $array['name']= array(new NotBlank());
        $array['user_name']= array(new NotBlank());
        $array['last_name']= array(new NotBlank());
        $array['address']= array(new NotBlank());
        $array['email']= array(new Email(),new NotBlank());
        if(isset($this->data['edit']) && $this->data['password']=='')
        {
            $array['user_password']= array();
        }
        else
        {
            $array['user_password']= array(new NotBlank(),new MinLength(6));
        }
        $collectionConstraint = new Collection($array);

        return array('validation_constraint' => $collectionConstraint);
    }

    public function getName()
    {
        return 'mycp_mycpbundle_client_casatype';
    }

    public function setData($data)
    {
        $this->data=$data;
    }
}
