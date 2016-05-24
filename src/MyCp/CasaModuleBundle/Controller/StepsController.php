<?php
/**
 * Created by PhpStorm.
 * User: neti
 * Date: 13/05/2016
 * Time: 11:23
 */

namespace MyCp\CasaModuleBundle\Controller;



use MyCp\CasaModuleBundle\Form\ownershipStep1Type;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use MyCp\mycpBundle\Entity\ownership;
use MyCp\mycpBundle\Entity\room;

/**
* @Route("/ownership/edit")
 */
class StepsController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="casa_module_edit_step1", path="/step1")
     */
 public function step1Action(Request $request){
     $em=$this->getDoctrine()->getManager();
     $user=$this->getUser();
     if(empty($user->getUserUserCasa()))
         return new NotFoundHttpException('El usuario no es usuario casa');
     $ownership=  $user->getUserUserCasa()[0]->getUserCasaOwnership();
     $form=$this->createForm(new ownershipStep1Type(),$ownership);
     $form->handleRequest($request);
     if($form->isValid()){
         $langs=$ownership->getOwnLangs();
         if(strlen($langs)==3){
             $ownership->setOwnLangs('0'.$langs);
         }
         elseif(strlen($langs)==2){
             $ownership->setOwnLangs('00'.$langs);
         }
         elseif(strlen($langs)==1){
             $ownership->setOwnLangs('000'.$langs);
         }

         $em->persist($ownership);
         $em->flush();
         return new JsonResponse('ok');

     }
     return $this->render('MyCpCasaModuleBundle:Ownership:step1.html.twig',array(
         'ownership'=>$ownership,
         'form'=>$form->createView()
     ));

 }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="save_step5", path="/save/step5")
     */
    public function saveStep4Action(Request $request){
        $em = $this->getDoctrine()->getManager();
        $rooms=$request->get('rooms');
        if(count($rooms)){
            $ownership = $em->getRepository('mycpBundle:ownership')->find($request->get('idown'));
            $i=1;
            foreach($rooms as $room){

                $obj= new room();
                $obj->setRoomNum($i);
                $obj->setRoomType($room['room_type']);
                $obj->setRoomBeds($room['number_beds']);
                $obj->setRoomPriceUpTo($room['price_high_season']);
                $obj->setRoomPriceDownTo($room['price_low_season']);
                $obj->setRoomPriceSpecial($room['price_special_season']);
                $obj->setRoomClimate(($room['room_climate']=='on'?'Aire acondicionado / Ventilador':''));
                $obj->setRoomAudiovisual(($room['room_audiovisual']=='on'?'TV+DVD / Video':'No'));
                $obj->setRoomSmoker(($room['room_smoker']=='on'?1:0));
                $obj->setRoomSmoker(($room['room_smoker']=='on'?1:0));
                $obj->setRoomActive(1);
                $obj->setRoomSafe(($room['room_safe']=='on'?1:0));
                $obj->setRoomBaby(($room['room_baby']=='on'?1:0));
                $obj->setRoomBathroom($room['room_bathroom']);
                $obj->setRoomStereo(($room['room_stereo']=='on'?1:0));
                $obj->setRoomWindows($room['room_window']);
                $obj->setRoomBalcony($room['room_balcony']);
                $obj->setRoomTerrace(($room['room_terrace']=='on'?1:0));
                $obj->setRoomYard(($room['room_yard']=='on'?1:0));

                $obj->setRoomOwnership($ownership);

                $em->persist($obj);
                $i++;
            }
            $em->flush();
        }
        return new JsonResponse([
            'success' => true
        ]);
    public function saveStep5Action(Request $request){
        print_r($request->get('hasBreakfast'));die;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="content_tab_step4", path="/content/step4")
     */
    public function getContentTabStep4Action(Request $request){
        return new JsonResponse([
            'num'=>$request->get('num'),
            'success' => true,
            'html' => $this->renderView('MyCpCasaModuleBundle:form:form4.html.twig', array('num'=>$request->get('num'))),
            'msg' => 'Nueva habitacion']);
    }

}