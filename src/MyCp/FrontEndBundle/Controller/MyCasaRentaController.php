<?php
/**
 * Created by PhpStorm.
 * User: neti
 * Date: 04/03/2016
 * Time: 9:43
 */

namespace MyCp\FrontEndBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class MyCasaRentaController extends Controller
{
 public function indexAction(Request $request){
     return $this->render('FrontEndBundle:mycasarenta:index.html.twig', array());
 }
}