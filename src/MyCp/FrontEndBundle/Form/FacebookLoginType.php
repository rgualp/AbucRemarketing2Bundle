<?php
/**
 * Created by PhpStorm.
 * User: Karel
 * Date: 9/17/14
 * Time: 2:03 PM
 */

namespace MyCp\FrontEndBundle\Form;

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
            ->add("name", "hidden")
            ->add("lastName", "hidden")
            ->add("gender", "hidden")
            ->add("email", "hidden")
            ->add("Continue to My Casa Particular", "submit", array("attr" => array("class" => "btn btn-success")));
    }
}
