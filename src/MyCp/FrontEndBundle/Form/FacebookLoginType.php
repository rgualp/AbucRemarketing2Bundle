<?php
/**
 * Created by PhpStorm.
 * User: Karel
 * Date: 9/17/14
 * Time: 2:03 PM
 */

namespace MyCp\FrontEndBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FacebookLoginType extends AbstractType
{
    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'FacebookLoginType';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod("POST")
            ->setAction($options['action'])
            ->add("name", "hidden", array('required'=> true, 'attr'=>array('class'=>'hide'), 'label_attr'=>array('class'=>'hide')))
            ->add("lastName", "hidden", array('required'=> true, 'attr'=>array('class'=>'hide'), 'label_attr'=>array('class'=>'hide')))
            ->add("gender", "hidden")
            ->add("email", "text", array('required'=> true, 'attr'=>array('class'=>'hide', 'style'=> 'width: 90%'), 'label_attr'=>array('class'=>'hide')))
           ->add("country", 'entity', array(
                'label'=> 'Country',
                'attr'=>array('class'=>'hide', 'style'=> 'width: 90%'),
               'label_attr'=>array('class'=>'hide'),
                'required'=> true,
                'class' => 'mycpBundle:country',
                'query_builder' => function (EntityRepository $er) {
                 return $er->createQueryBuilder('c')
                ->orderBy('c.co_code', 'ASC');
        //  ->groupBy('p.perm_category');
               }


            ))
            ->add("language", "hidden")
            /*->add("Continue to My Casa Particular", "submit", array("attr" => array("class" => "btn btn-success")))*/;
    }
}
