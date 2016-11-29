<?php
namespace MyCp\mycpBundle\Form;

use MyCp\FrontEndBundle\Helpers\PaymentHelper;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\generalReservationRepository;
use MyCp\mycpBundle\Helpers\FormMode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class touristFailureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reservation', 'entity', array(
                'label'=>'Reserva:',
                'class' => 'MyCp\mycpBundle\Entity\generalReservation',
                'query_builder' => function (generalReservationRepository  $er) {
                    return $er->createQueryBuilder('o')
                        ->join("o.own_reservations", "r")
                        ->join("r.own_res_reservation_booking", "b")
                        ->join("b.payments", "p")
                        ->where("o.gen_res_status = :reservedStatus")
                        ->andWhere("(p.status = :successfulPayment or p.status = :processedPayment)")
                        ->orderBy('o.gen_res_id', 'DESC')
                        ->setParameter("reservedStatus", generalReservation::STATUS_RESERVED)
                        ->setParameter("successfulPayment", PaymentHelper::STATUS_SUCCESS)
                        ->setParameter("processedPayment", PaymentHelper::STATUS_PROCESSED);
                },
                'property' => 'casDateAndTourist',
                'required' => false,
                'multiple' => false,
                'attr' => array("class" => "select input-block-level")
            ))
            ->add('description',null,array('label'=>'Descripción:','attr'=>array('class'=>'input-block-level')));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\touristFailure'
        ));
    }

    public function getName()
    {
        return 'mycp_mycpbundle_touristfailuretype';
    }
}
