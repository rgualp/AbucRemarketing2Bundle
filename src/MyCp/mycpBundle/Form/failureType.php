<?php
namespace MyCp\mycpBundle\Form;

use MyCp\FrontEndBundle\Helpers\PaymentHelper;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\generalReservationRepository;
use MyCp\mycpBundle\Entity\nomenclatorRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;

class failureType extends AbstractType
{
    private $accommodationId;

    public function __construct($accommodationId)
    {
        $this->accommodationId = $accommodationId;

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', 'entity', array(
                'label'=>'Tipo:',
                'class' => 'MyCp\mycpBundle\Entity\nomenclator',
                'query_builder' => function (nomenclatorRepository $er) {
                    return $er->createQueryBuilder('n')
                        ->innerJoin('n.translations', 't')
                        ->join("t.nom_lang_id_lang", "lang")
                        ->where("n.nom_category = 'failureType'")
                        ->andWhere("lang.lang_code = 'ES'");
                },
                'property' => 'translations[0].nom_lang_description',
                'required' => true,
                'multiple' => false
            ))
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
                        ->andWhere("o.gen_res_date >= :date")
                        ->andWhere("o.gen_res_own_id = :accommodationId")
                        ->orderBy('o.gen_res_id', 'DESC')
                        ->setParameter("reservedStatus", generalReservation::STATUS_RESERVED)
                        ->setParameter("successfulPayment", PaymentHelper::STATUS_SUCCESS)
                        ->setParameter("processedPayment", PaymentHelper::STATUS_PROCESSED)
                        ->setParameter("accommodationId", $this->accommodationId)
                        ->setParameter("date", new \DateTime('-3 month'))
                        ;
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
            'data_class' => 'MyCp\mycpBundle\Entity\failure'
        ));
    }

    public function getName()
    {
        return 'mycp_mycpbundle_failuretype';
    }
}
