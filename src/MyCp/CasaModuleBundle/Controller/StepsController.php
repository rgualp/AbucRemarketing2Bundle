<?php
/**
 * Created by PhpStorm.
 * User: neti
 * Date: 13/05/2016
 * Time: 11:23
 */

namespace MyCp\CasaModuleBundle\Controller;



use Doctrine\Common\Collections\ArrayCollection;
use MyCp\CasaModuleBundle\Form\ownershipStep1Type;
use MyCp\CasaModuleBundle\Form\ownershipStepPhotosType;
use MyCp\mycpBundle\Entity\owner;
use MyCp\mycpBundle\Entity\ownerAccommodation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use MyCp\mycpBundle\Entity\ownership;
use MyCp\mycpBundle\Entity\room;
use MyCp\mycpBundle\Entity\ownershipDescriptionLang;
use MyCp\mycpBundle\Service\TranslatorResponseStatusCode;

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
        $hasBreakfast = $request->get('hasBreakfast');
        $idAccommodation = $request->get('idAccommodation');
        $breakfastPrice = $request->get('breakfastPrice');
        $hasDinner = $request->get('hasDinner');
        $dinnerPriceFrom = $request->get('dinnerPriceFrom');
        $dinnerPriceTo = $request->get('dinnerPriceTo');
        $hasParking = $request->get('hasParking');
        $parkingPrice = $request->get('parkingPrice');
        $hasJacuzzy = $request->get('hasJacuzzy');
        $hasSauna = $request->get('hasSauna');
        $hasPool = $request->get('hasPool');
        $hasParkingBike = $request->get('hasParkingBike');
        $hasPet = $request->get('hasPet');
        $hasLaundry = $request->get('hasLaundry');
        $hasEmail = $request->get('hasEmail');

        $em = $this->getDoctrine()->getManager();
        $accommodation = $em->getRepository('mycpBundle:ownership')->find($idAccommodation);

        $accommodation->setOwnFacilitiesBreakfast($hasBreakfast)
            ->setOwnFacilitiesBreakfastPrice($breakfastPrice)
            ->setOwnFacilitiesDinner($hasDinner)
            ->setOwnFacilitiesDinnerPriceFrom($dinnerPriceFrom)
            ->setOwnFacilitiesDinnerPriceTo($dinnerPriceTo)
            ->setOwnFacilitiesParking($hasParking)
            ->setOwnFacilitiesParkingPrice($parkingPrice)
            ->setOwnWaterJacuzee($hasJacuzzy)
            ->setOwnWaterSauna($hasSauna)
            ->setOwnWaterPiscina($hasPool)
            ->setOwnDescriptionBicycleParking($hasParkingBike)
            ->setOwnDescriptionPets($hasPet)
            ->setOwnDescriptionLaundry($hasLaundry)
            ->setOwnDescriptionInternet($hasEmail);

        $em->persist($accommodation);
        $em->flush();

        return new JsonResponse([
            'success' => true
        ]);
    }
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="save_step6", path="/save/step6")
     */
    public function saveStep6Action(Request $request){
        $em=$this->getDoctrine()->getManager();
        $ownership=  $this->getUser()->getUserUserCasa()[0]->getUserCasaOwnership();
        $photosForm=$this->createForm(new ownershipStepPhotosType(),$ownership,array( 'action' => $this->generateUrl('save_step6'), 'attr' =>['id'=>'mycp_mycpbundle_ownership_step_photos']));
        $photosForm->handleRequest($request);
        if($photosForm->isValid()){
            $ownership->setPhotos(new ArrayCollection());
        foreach($request->files->get('mycp_mycpbundle_ownership_step_photos')['photos'] as $index=>$file){
        $desc=  $request->get('mycp_mycpbundle_ownership_step_photos')['photos'][$index]['description'];
        $file=  $file['file'];
//        try{
            $post=array();
            $language = $em->getRepository('mycpBundle:lang')->findAll();
            $translator = $this->get("mycp.translator.service");
            foreach($language as $lang){
                if($lang->getLangCode()=='ES'){
                    $post['description_'.$lang->getLangId()]=$desc;
                }
                else{
                    $response = $translator->translate($desc, 'ES', $lang->getLangCode());
                    if($response->getCode() == TranslatorResponseStatusCode::STATUS_200)
                        $post['description_'.$lang->getLangId()]=$response->getTranslation();
                    else $post['description_'.$lang->getLangId()]=$desc;

                }
            }
            $em->getRepository("mycpBundle:ownershipPhoto")->createPhotoFromRequest($ownership,$file,$this->get('service_container'),$post);
//          }
//        catch (\Exception $exc){
//            return new JsonResponse([
//                'success' => false,
//                'message'=>$exc->getMessage()
//            ]);
//        }
        }

        }

        return new JsonResponse([
            'success' => true
        ]);
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
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="save_description", path="/save/description")
     */
    public function saveDescriptionAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $ownership = $em->getRepository('mycpBundle:ownership')->find($request->get('idown'));
        $description=$request->get('comment-one').' '.$request->get('comment-two').' '.$request->get('comment-three').' '.$request->get('comment-four');

        $language = $em->getRepository('mycpBundle:lang')->findAll();
        $translator = $this->get("mycp.translator.service");
        foreach($language as $lang){
            $ownershipDescriptionLang= new ownershipDescriptionLang();
            if($lang->getLangCode()=='ES'){
                $ownershipDescriptionLang->setOdlOwnership($ownership)
                    ->setOdlIdLang($lang)                   //id del lenguage
                    ->setOdlDescription($request->get('comment-one'))                    //descripcion corta que corresponde al primer parrafo
                    ->setOdlBriefDescription($description)
                    ->setOdlAutomaticTranslation(0);
            }
            else{
                $response = $translator->translate($description, 'ES', $lang->getLangCode());
                if($response->getCode() == TranslatorResponseStatusCode::STATUS_200)
                    $briefDescription = $response->getTranslation();

                $response = $translator->translate($request->get('comment-one'), 'ES', $lang->getLangCode());
                if($response->getCode() == TranslatorResponseStatusCode::STATUS_200)
                    $shortDescription = $response->getTranslation();


                $ownershipDescriptionLang->setOdlOwnership($ownership)
                    ->setOdlIdLang($lang)                   //id del lenguage
                    ->setOdlDescription($shortDescription)                    //descripcion corta que corresponde al primer parrafo
                    ->setOdlBriefDescription($briefDescription)
                    ->setOdlAutomaticTranslation(1);
            }
            $em->persist($ownershipDescriptionLang);
        }
        $em->flush();
        return new JsonResponse([
            'success' => true,
        ]);
    }


    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="save_step7", path="/save/step7")
     */
    public function saveStep7Action(Request $request){
        $idAccommodation = $request->get('idAccommodation');
        $mobile = $request->get('mobile');
        $phone = $request->get('phone');
        $mainStreet = $request->get('mainStreet');
        $streetNumber = $request->get('streetNumber');
        $between1 = $request->get('between1');
        $between2 = $request->get('between2');
        $municipalityId = $request->get('municipalityId');
        $provinceId = $request->get('provinceId');
        $email2 = $request->get('email2');
        $secondOwner = $request->get('secondOwner');

        $em = $this->getDoctrine()->getManager();
        $accommodation = $em->getRepository('mycpBundle:ownership')->find($idAccommodation);

        $accommodation->setOwnMobileNumber($mobile)
            ->setOwnEmail2($email2)
            ->setOwnHomeowner2($secondOwner);
        $em->persist($accommodation);

        $ownerAccommodation = $em->getRepository("mycpBundle:ownerAccommodation")->getMainOwner($idAccommodation);
        $owner = new owner();

        if($ownerAccommodation != null)
        {
            $owner = $ownerAccommodation->getOwner();
        }
        else{
            $ownerAccommodation = new ownerAccommodation();
        }

        $municipality = $em->getRepository("mycpBundle:municipality")->find($municipalityId);
        $province = $em->getRepository("mycpBundle:province")->find($provinceId);

        $owner->setAddressBetween1($between1)
            ->setAddressBetween2($between2)
            ->setPhone($phone)
            ->setMobile($mobile)
            ->setEmail2($email2)
            ->setMunicipality($municipality)
            ->setProvince($province)
            ->setAddressMainStreet($mainStreet)
            ->setAddressStreetNumber($streetNumber);

        $em->persist($owner);

        $ownerAccommodation->setAccommodation($accommodation)
            ->setOwner($owner);

        $em->persist($ownerAccommodation);

        $em->flush();

        return new JsonResponse([
            'success' => true
        ]);
    }
}