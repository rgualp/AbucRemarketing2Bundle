<?php
namespace MyCp\mycpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\MinLength;
use Symfony\Component\Validator\Constraints\NotBlank;


class reservationType extends AbstractType
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

        $array_rooms=array();
        $a=1;
        foreach($this->data['rooms'] as $room)
        {
            $array_rooms[$room->getRoomId()]='Habitación '.$a;
            $a++;
        }

        $array_users=array();
        foreach($this->data['users'] as $user)
        {
            $array_users[$user->getUserId()]=$user->getUserName();
            $a++;
        }

        $builder->add('reservation_ownership',
        'choice',array('empty_value' => '','choices'=>$array_ownerships,'label'=>'Casa:','attr'=>array('class'=>'input-block-level reservation_ownership')));
        $builder->add('selected_room','choice',array('choices'=>$array_rooms,'empty_value' => '','label'=>'Habitación id:','attr'=>array('class'=>'input-block-level selected_room')));
        $builder->add('count_adults','integer',array('label'=>'Número de adultos:','attr'=>array('class'=>'input-block-level')));
        $builder->add('count_childrens','integer',array('label'=>'Número de niños:','attr'=>array('class'=>'input-block-level')));
        $builder->add('reservation_from_date',null,array('label'=>'Fecha de entrada:','attr'=>array('class'=>'input-block-level datepicker')));
        $builder->add('reservation_to_date',null,array('label'=>'Fecha de salida:','attr'=>array('class'=>'input-block-level datepicker')));
        $builder->add('night_price','integer',array('label'=>'Precio x noche:','attr'=>array('class'=>'input-block-level')));
        $builder->add('percent','integer',array('label'=>'Comisión x%:','attr'=>array('class'=>'input-block-level')));
        $builder->add('user','choice',array('choices'=>$array_users,'empty_value' => '','label'=>'Usuario:','attr'=>array('class'=>'input-block-level user')));
    }

    public function getDefaultOptions(array $options)
    {
        $array=array();
        $array['reservation_ownership']= array(new NotBlank());
        $array['selected_room']= array(new NotBlank());
        $array['count_adults']= array(new NotBlank());
        $array['count_childrens']= array(new NotBlank());
        $array['reservation_from_date']= array(new NotBlank());
        $array['reservation_to_date']= array(new NotBlank());
        $array['night_price']= array(new NotBlank());
        $array['percent']= array(new NotBlank());
        $array['user']= array(new NotBlank());
        $collectionConstraint = new Collection($array);

        return array('validation_constraint' => $collectionConstraint);
    }

    public function getName()
    {
        return 'mycp_mycpbundle_reservationtype';
    }

    public function setData($data)
    {
        $this->data=$data;
    }
}
