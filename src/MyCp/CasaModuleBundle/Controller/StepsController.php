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
     * @Route(name="save_step4", path="/save/step4")
     */
    public function saveStep4Action(Request $request){
        $em = $this->getDoctrine()->getManager();
        $rooms=$request->get('rooms');
        if(count($rooms)){
            $ownership = $em->getRepository('mycpBundle:ownership')->find($request->get('idown'));
            $i=1;
            foreach($rooms as $room){
                //Se esta modificando
                if(isset($room['idRoom'])){
                    $ownership_room = $em->getRepository('mycpBundle:room')->find($room['idRoom']);
                    if(isset($room['room_type']))
                        $ownership_room->setRoomType($room['room_type']);
                    if(isset($room['number_beds']))
                        $ownership_room->setRoomBeds($room['number_beds']);
                    if(isset($room['price_high_season']))
                        $ownership_room->setRoomPriceUpTo($room['price_high_season']);
                    if(isset($room['price_low_season']))
                        $ownership_room->setRoomPriceDownTo($room['price_low_season']);
                    if(isset($room['price_special_season']))
                        $ownership_room->setRoomPriceSpecial($room['price_special_season']);
                    $ownership_room->setRoomClimate((isset($room['room_climate']))?($room['room_climate']=='on'?'Aire acondicionado / Ventilador':''):'');
                    $ownership_room->setRoomAudiovisual((isset($room['room_audiovisual']))?($room['room_audiovisual']=='on'?'TV+DVD / Video':''):'');

                    $ownership_room->setRoomSmoker((isset($room['room_smoker']))?($room['room_smoker']=='on'?1:0):0);
                    $ownership_room->setRoomSafe((isset($room['room_safe']))?($room['room_safe']=='on'?1:0):0);
                    $ownership_room->setRoomBaby((isset($room['room_baby']))?($room['room_baby']=='on'?1:0):0);
                    if(isset($room['room_bathroom']))
                        $ownership_room->setRoomBathroom($room['room_bathroom']);
                    $ownership_room->setRoomStereo((isset($room['room_stereo']))?($room['room_stereo']=='on'?1:0):0);
                    if(isset($room['room_window']))
                        $ownership_room->setRoomWindows($room['room_window']);
                    if(isset($room['room_balcony']))
                        $ownership_room->setRoomBalcony($room['room_balcony']);
                    $ownership_room->setRoomTerrace((isset($room['room_terrace']))?($room['room_terrace']=='on'?1:0):0);
                    $ownership_room->setRoomYard((isset($room['room_yard']))?($room['room_yard']=='on'?1:0):0);
                    $em->persist($ownership_room);
                }
                else{
                    //Se esta insertando
                    $obj= new room();
                    $obj->setRoomNum($i);
                    $obj->setRoomType($room['room_type']);
                    $obj->setRoomBeds($room['number_beds']);
                    $obj->setRoomPriceUpTo($room['price_high_season']);
                    $obj->setRoomPriceDownTo($room['price_low_season']);
                    $obj->setRoomPriceSpecial($room['price_special_season']);
                    $obj->setRoomClimate((isset($room['room_climate']))?($room['room_climate']=='on'?'Aire acondicionado / Ventilador':''):'');
                    $obj->setRoomAudiovisual((isset($room['room_audiovisual']))?($room['room_audiovisual']=='on'?'TV+DVD / Video':''):'');
                    $obj->setRoomSmoker((isset($room['room_smoker']))?($room['room_smoker']=='on'?1:0):0);
                    $obj->setRoomActive(1);
                    $obj->setRoomSafe((isset($room['room_safe']))?($room['room_safe']=='on'?1:0):0);
                    $obj->setRoomBaby((isset($room['room_baby']))?($room['room_baby']=='on'?1:0):0);
                    $obj->setRoomBathroom($room['room_bathroom']);
                    $obj->setRoomStereo((isset($room['room_stereo']))?($room['room_stereo']=='on'?1:0):0);
                    $obj->setRoomWindows($room['room_window']);
                    $obj->setRoomBalcony($room['room_balcony']);
                    $obj->setRoomTerrace((isset($room['room_terrace']))?($room['room_terrace']=='on'?1:0):0);
                    $obj->setRoomYard((isset($room['room_yard']))?($room['room_yard']=='on'?1:0):0);
                    $obj->setRoomOwnership($ownership);
                    $em->persist($obj);
                }
                $i++;
            }
            $em->flush();
        }
        return new JsonResponse([
            'success' => true
        ]);
    }
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="save_step5", path="/save/step5")
     */
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
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="change_active_room", path="/change/active/room")
     */
    public function changeActiveRoomAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $room = $em->getRepository('mycpBundle:room')->find($request->get('idroom'));
        $room->setRoomActive(($request->get('val')=='false'?0:1));

        $em->persist($room);
        $em->flush();
        return new JsonResponse([
            'success' => true,
            'msg' => 'Se ha cambiado el estado'
        ]);
    }

}