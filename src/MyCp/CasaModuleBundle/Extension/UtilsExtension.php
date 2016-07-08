<?php

namespace MyCp\CasaModuleBundle\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class UtilsExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
//        $this->request = $this->container->get('request');

    }

    public function getName()
    {
        return 'Utils';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('activeClass', array($this, 'getActiveClass')),
            new \Twig_SimpleFunction('errorsForm', array($this, 'getErrorsForm'))

        );
    }

    public function getActiveClass($strRoutes){
        $route = $this->container->get('request_stack')->getCurrentRequest()->get('_route');
        $pos = strpos($strRoutes, $route);
        if ($pos === false)
            return '';
        return 'active';
    }

    public function getErrorsForm(\Symfony\Component\Form\Form $form) {
        $errors = array();
        foreach ($form->getErrors() as $key => $error) {
            $errors['form'][] = $error->getMessage();
        }

        foreach ($form->all() as $field) {
            if (!$field->isValid()) {
                $errorFields = array();
                foreach ($field->getErrors() as $key => $error) {
                    $errorFields[] = $error->getMessage();
                }
                $errors['fields'][$field->getName()] = $errorFields;
            }
        }
        return $errors;
    }

}