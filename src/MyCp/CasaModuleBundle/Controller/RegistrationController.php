<?php
/**
 * Created by PhpStorm.
 * User: neti
 * Date: 04/05/2016
 * Time: 12:01
 */

namespace MyCp\CasaModuleBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RegistrationController extends Controller
{

    public function registerAction(){
    return $this->render('MyCpCasaModuleBundle:Registration:register.html.twig',
        array());
    }
}