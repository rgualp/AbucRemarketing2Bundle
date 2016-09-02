<?php

namespace MyCp\PartnerBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FilterOwnershipType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        for($i=0;$i<=10;$i++){
            $huesp[$i]=$i;
        }
        for($i=0;$i<=10;$i++){
            $child[$i]=$i;
        }
        for($i=0;$i<100;$i++){
            $age[$i]=$i;
        }

        $builder
            ->add('destination', 'entity', array(
                'class' => 'MyCp\mycpBundle\Entity\destination',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('d')->orderBy('d.des_name', 'ASC');
                },
                'empty_data'  => null,
                'empty_value' => "",
                'property' => 'des_name',
                'required' => true,
                'multiple' => false
            ))
            ->add('arrival', 'text', array('required' => true, 'attr' => array('class' => 'form-control input-date')))
            ->add('exit', 'text', array('required' => true, 'attr' => array('class' => 'form-control input-date')))
            ->add('huesp','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'choices'=>$huesp
            ))
            ->add('child','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'choices'=>$child
            ))
            ->add('age','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'choices'=>$age
            ))
            ->add('room','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'choices'=>$age
            ))
            ->add('category','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'choices'=>$age
            ))
            ->add('property','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'choices'=>$age
            ))
            ->add('bed','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'choices'=>$age
            ))
            ->add('room_type','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'choices'=>$age
            ))
            ->add('bath_room','choice', array(
                'empty_data'  => null,
                'empty_value' => "",
                'choices'=>$age
            ))
            ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'requests_ownership_filter';
    }
}
