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
use MyCp\mycpBundle\Entity\ownershipStatistics;
use MyCp\mycpBundle\Entity\ownershipStatus;
use MyCp\mycpBundle\Entity\photoLang;
use MyCp\mycpBundle\Entity\unavailabilityDetails;
use MyCp\mycpBundle\Helpers\FileIO;
use MyCp\mycpBundle\Helpers\Images;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use MyCp\mycpBundle\Entity\ownership;
use MyCp\mycpBundle\Entity\room;
use MyCp\mycpBundle\Entity\ownershipDescriptionLang;
use MyCp\mycpBundle\Service\TranslatorResponseStatusCode;
use MyCp\mycpBundle\Entity\photo;

/**
 * @Route("/ownership/edit")
 */
class StepsController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="show_casa", path="/panel/casa")
     */
    public function showCasaAction(Request $request)
    {
        $user=$this->getUser();
        if(empty($user->getUserUserCasa()))
            return new NotFoundHttpException('El usuario no es usuario casa');
        $ownership=  $user->getUserUserCasa()[0]->getUserCasaOwnership();
        $form=$this->createForm(new ownershipStep1Type(),$ownership);
        //        die(dump($form['own_langs1']));
        $langs=array();
        if($ownership->getOwnLangs()){
            if(substr($ownership->getOwnLangs(),0,1))
                $langs[]='1000';
            if(substr($ownership->getOwnLangs(),1,1))
                $langs[]='0100';
            if(substr($ownership->getOwnLangs(),2,1))
                $langs[]='0010';
            if(substr($ownership->getOwnLangs(),3,1))
                $langs[]='0001';
        }
//        die(dump($langs));
//        if($ownership->getOwnStatus()->getStatusId()==ownershipStatus::STATUS_ACTIVE){
            return $this->render('MyCpCasaModuleBundle:Steps:step2.html.twig', array(
                'ownership'=>$ownership,
                'dashboard'=>$ownership->getOwnStatus()->getStatusId()==ownershipStatus::STATUS_ACTIVE,
                'form'=>$form->createView(),
                'langs'=>$langs
            ));
//        }

    }
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="casa_show_photos", path="/panel/photos")
     */
    public function showPhotosAction(Request $request)
    {
        $user=$this->getUser();
        if(empty($user->getUserUserCasa()))
            return new NotFoundHttpException('El usuario no es usuario casa');
        $ownership=  $user->getUserUserCasa()[0]->getUserCasaOwnership();
        $photosForm=$this->createForm(new ownershipStepPhotosType(),$ownership,array( 'action' => $this->generateUrl('save_step6'), 'attr' =>['id'=>'mycp_mycpbundle_ownership_step_photos']));

//        if($ownership->getOwnStatus()->getStatusId()==ownershipStatus::STATUS_ACTIVE){
            return $this->render('MyCpCasaModuleBundle:Steps:step6.html.twig', array(
                'ownership'=>$ownership,
                'dashboard'=>$ownership->getOwnStatus()->getStatusId()==ownershipStatus::STATUS_ACTIVE,
                'photoForm'=>$photosForm->createView()
            ));
//        }

    }
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="casa_module_edit_step1", path="/step1")
     */
    public function step1Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (empty($user->getUserUserCasa()))
            return new NotFoundHttpException('El usuario no es usuario casa');
        $ownership = $user->getUserUserCasa()[0]->getUserCasaOwnership();
        $form = $this->createForm(new ownershipStep1Type(), $ownership);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $langs = $ownership->getOwnLangs();
            if (strlen($langs) == 3) {
                $ownership->setOwnLangs('0' . $langs);
            } elseif (strlen($langs) == 2) {
                $ownership->setOwnLangs('00' . $langs);
            } elseif (strlen($langs) == 1) {
                $ownership->setOwnLangs('000' . $langs);
            }

            if($ownership->getOwnCommissionPercent() == null || $ownership->getOwnCommissionPercent() == "")
                $ownership->setOwnCommissionPercent(20);

            $em->persist($ownership);
            $em->flush();
            return new JsonResponse('ok');

        }
        return $this->render('MyCpCasaModuleBundle:Ownership:step1.html.twig', array(
            'ownership' => $ownership,
            'form' => $form->createView()
        ));

    }
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="show_room", path="/panel/room")
     */
    public function showRoomAction(Request $request)
    {
        $ownership = $this->getUser()->getUserUserCasa()[0]->getUserCasaOwnership();
        return $this->render('MyCpCasaModuleBundle:Steps:step4.html.twig', array(
            'ownership'=>$ownership,
            'dashboard'=>true
        ));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="save_step4", path="/save/step4")
     */
    public function saveStep4Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $rooms = $request->get('rooms');
        $ids=array();
        $avgRoomPrice = 0;
        if (count($rooms)) {
            $ownership = $em->getRepository('mycpBundle:ownership')->find($request->get('idown'));
            $commisionPercent = $ownership->getOwnCommissionPercent() / 100;
            $i = 1;
            foreach ($rooms as $room) {
                //Se esta modificando
                if (isset($room['idRoom']) && $room['idRoom'] !='') {
                    $ownership_room = $em->getRepository('mycpBundle:room')->find($room['idRoom']);
                    if (isset($room['room_type']))
                        $ownership_room->setRoomType($room['room_type']);
                    if (isset($room['number_beds']))
                        $ownership_room->setRoomBeds($room['number_beds']);
                    if (isset($room['price_high_season']))
                        $ownership_room->setRoomPriceUpTo($room['price_high_season']);
                    if (isset($room['price_low_season']))
                        $ownership_room->setRoomPriceDownTo($room['price_low_season']);
                    if (isset($room['price_special_season']))
                        $ownership_room->setRoomPriceSpecial($room['price_special_season']);
                    $ownership_room->setRoomClimate((isset($room['room_climate'])) ? ($room['room_climate'] == 'on' ? 'Aire acondicionado / Ventilador' : '') : '');
                    $ownership_room->setRoomAudiovisual((isset($room['room_audiovisual'])) ? ($room['room_audiovisual'] == 'on' ? 'TV+DVD / Video' : '') : '');

                    $ownership_room->setRoomSmoker((isset($room['room_smoker'])) ? ($room['room_smoker'] == 'on' ? 1 : 0) : 0);
                    $ownership_room->setRoomSafe((isset($room['room_safe'])) ? ($room['room_safe'] == 'on' ? 1 : 0) : 0);
                    $ownership_room->setRoomBaby((isset($room['room_baby'])) ? ($room['room_baby'] == 'on' ? 1 : 0) : 0);
                    if (isset($room['room_bathroom']))
                        $ownership_room->setRoomBathroom($room['room_bathroom']);
                    $ownership_room->setRoomStereo((isset($room['room_stereo'])) ? ($room['room_stereo'] == 'on' ? 1 : 0) : 0);
                    if (isset($room['room_window']))
                        $ownership_room->setRoomWindows($room['room_window']);
                    if (isset($room['room_balcony']))
                        $ownership_room->setRoomBalcony($room['room_balcony']);
                    $ownership_room->setRoomTerrace((isset($room['room_terrace'])) ? ($room['room_terrace'] == 'on' ? 1 : 0) : 0);
                    $ownership_room->setRoomYard((isset($room['room_yard'])) ? ($room['room_yard'] == 'on' ? 1 : 0) : 0);
                    $em->persist($ownership_room);
                    $em->flush();

                    $avgRoomPrice += $ownership_room->getRoomPriceDownTo();
                } else {
                    //Se esta insertando
                    $obj = new room();
                    $obj->setRoomNum($i);
                    $obj->setRoomType($room['room_type']);
                    $obj->setRoomBeds($room['number_beds']);
                    $obj->setRoomPriceUpTo($room['price_high_season']);
                    $obj->setRoomPriceDownTo($room['price_low_season']);
                    $obj->setRoomPriceSpecial($room['price_special_season']);
                    $obj->setRoomClimate((isset($room['room_climate'])) ? ($room['room_climate'] == 'on' ? 'Aire acondicionado / Ventilador' : '') : '');
                    $obj->setRoomAudiovisual((isset($room['room_audiovisual'])) ? ($room['room_audiovisual'] == 'on' ? 'TV+DVD / Video' : '') : '');
                    $obj->setRoomSmoker((isset($room['room_smoker'])) ? ($room['room_smoker'] == 'on' ? 1 : 0) : 0);
                    $obj->setRoomActive(1);
                    $obj->setRoomSafe((isset($room['room_safe'])) ? ($room['room_safe'] == 'on' ? 1 : 0) : 0);
                    $obj->setRoomBaby((isset($room['room_baby'])) ? ($room['room_baby'] == 'on' ? 1 : 0) : 0);
                    $obj->setRoomBathroom($room['room_bathroom']);
                    $obj->setRoomStereo((isset($room['room_stereo'])) ? ($room['room_stereo'] == 'on' ? 1 : 0) : 0);
                    $obj->setRoomWindows($room['room_window']);
                    $obj->setRoomBalcony($room['room_balcony']);
                    $obj->setRoomTerrace((isset($room['room_terrace'])) ? ($room['room_terrace'] == 'on' ? 1 : 0) : 0);
                    $obj->setRoomYard((isset($room['room_yard'])) ? ($room['room_yard'] == 'on' ? 1 : 0) : 0);
                    $obj->setRoomOwnership($ownership);
                    $em->persist($obj);
                    $em->flush();
                    $ids[]=$obj->getRoomId();

                    $avgRoomPrice += $obj->getRoomPriceDownTo();
                }

                $i++;
            }

            //Calcular la categoria de la casa
            $avgRoomPrice = $avgRoomPrice / count($rooms);
            $accommodationCategory = "";

            if($avgRoomPrice < 35)
                $accommodationCategory = ownership::ACCOMMODATION_CATEGORY_ECONOMIC;
            elseif ($avgRoomPrice >= 32 && $avgRoomPrice < 50)
                $accommodationCategory = ownership::ACCOMMODATION_CATEGORY_MIDDLE_RANGE;
            else
                $accommodationCategory = ownership::ACCOMMODATION_CATEGORY_PREMIUM;

            $ownership->setOwnCategory($accommodationCategory);
            $em->persist($ownership);
            $em->flush();
        }
        return new JsonResponse([
            'success' => true,
            'ids'=>$ids
        ]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="save_facilities", path="/save/step5")
     */
    public function saveStep5Action(Request $request)
    {
        $hasBreakfast = ($request->get('hasBreakfast') == "true");
        $idAccommodation = $request->get('idAccommodation');
        $breakfastPrice = $request->get('breakfastPrice');
        $hasDinner = ($request->get('hasDinner') == "true");
        $dinnerPriceFrom = $request->get('dinnerPriceFrom');
        $dinnerPriceTo = $request->get('dinnerPriceTo');
        $hasParking = ($request->get('hasParking') == "true");
        $parkingPrice = $request->get('parkingPrice');
        $hasJacuzzy = ($request->get('hasJacuzzy') == "true");
        $hasSauna = ($request->get('hasSauna') == "true");
        $hasPool = ($request->get('hasPool') == "true");
        $hasParkingBike = ($request->get('hasParkingBike') == "true");
        $hasPet = ($request->get('hasPet') == "true");
        $hasLaundry = ($request->get('hasLaundry') == "true");
        $hasEmail = ($request->get('hasEmail') == "true");

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

        if($request->get('dashboard')){
            return $this->render('MyCpCasaModuleBundle:Steps:step5.html.twig', array(
                'ownership'=>$accommodation,
                'dashboard'=>true
            ));
        }
        else {
            return new JsonResponse([
                'success' => true
            ]);
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="save_step6", path="/save/step6")
     */
    public function saveStep6Action(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        //$ownership = $this->getUser()->getUserUserCasa()[0]->getUserCasaOwnership();
        $user = $this->getUser();
        if(empty($user->getUserUserCasa()))
            return new NotFoundHttpException('El usuario no es usuario casa');
        $ownership=  $user->getUserUserCasa()[0]->getUserCasaOwnership();

        $photosForm = $this->createForm(new ownershipStepPhotosType(), $ownership, array('action' => $this->generateUrl('save_step6'), 'attr' => ['id' => 'mycp_mycpbundle_ownership_step_photos']));
        $photosForm->handleRequest($request);
        if ($photosForm->isValid()) {
            $ownership->setPhotos(new ArrayCollection());
           if(!empty($request->files->get('mycp_mycpbundle_ownership_step_photos'))){
           foreach ($request->files->get('mycp_mycpbundle_ownership_step_photos')['photos'] as $index => $file) {
                if($request->get('mycp_mycpbundle_ownership_step_photos')['photos'][$index]['own_pho_id']=='') {
                   $desc = $request->get('mycp_mycpbundle_ownership_step_photos')['photos'][$index]['description'];
                    $file = $file['file'];
//        try{
                    $post = array();
                    $language = $em->getRepository('mycpBundle:lang')->findAll();
                    $translator = $this->get("mycp.translator.service");
                    foreach ($language as $lang) {
                        if ($lang->getLangCode() == 'ES') {
                            $post['description_' . $lang->getLangId()] = $desc;
                        } else {
                            try {
                                $response = $translator->translate($desc, 'ES', $lang->getLangCode());
                                if ($response->getCode() == TranslatorResponseStatusCode::STATUS_200)
                                    $post['description_' . $lang->getLangId()] = $response->getTranslation();
                                else $post['description_' . $lang->getLangId()] = $desc;
                            }
                            catch (\Exception $exc){
                                $post['description_' . $lang->getLangId()] = $desc;
                            }

                        }
                    }
                    $em->getRepository("mycpBundle:ownershipPhoto")->createPhotoFromRequest($ownership, $file, $this->get('service_container'), $post);
                }
                else{

                    $ownPhoto=$em->getRepository('mycpBundle:ownershipPhoto')->find($request->get('mycp_mycpbundle_ownership_step_photos')['photos'][$index]['own_pho_id']);
                    if($file['file']!=null){
                      $file=$file['file'];
                      $dir = $this->get('service_container')->getParameter('ownership.dir.photos');
                      $dir_thumbs =$this->get('service_container')->getParameter('ownership.dir.thumbnails');
                      $dir_watermark =$this->get('service_container')->getParameter('dir.watermark');
                      $photo_size = $this->get('service_container')->getParameter('ownership.dir.photos.size');
                      $thumbs_size = $this->get('service_container')->getParameter('thumbnail.size');
                      $dirUserPhoto = $this->get('service_container')->getParameter('user.dir.photos');
                      $userPhotoSize = $this->get('service_container')->getParameter('user.photo.size');
                      $fileName = uniqid('ownership-') . '-photo.jpg';
                      $file->move($dir, $fileName);
                      $photo=$ownPhoto->getOwnPhoPhoto();
                      FileIO::deleteFile($dir . $photo->getPhoName());
                      FileIO::deleteFile($dir_thumbs . $photo->getPhoName());
                      FileIO::deleteFile($dir_watermark . $photo->getPhoName());
                      $photo->setPhoName($fileName);
                      //Creando thumbnail, redimensionando y colocando marca de agua
                      Images::createThumbnail($dir . $fileName, $dir_thumbs . $fileName, $thumbs_size);
                      Images::resizeAndWatermark($dir, $fileName, $dir_watermark, $photo_size, $this->get('service_container'));
                      $em->persist($photo);
                  }
                    if($request->get('mycp_mycpbundle_ownership_step_photos')['photos'][$index]['description']!=''){
                        $photo=$ownPhoto->getOwnPhoPhoto();
                        $desc = $request->get('mycp_mycpbundle_ownership_step_photos')['photos'][$index]['description'];
                        $language = $em->getRepository('mycpBundle:lang')->findAll();
                        $translator = $this->get("mycp.translator.service");
                        if(count($photo->getPhotoLangs())>0){
                         foreach($photo->getPhotoLangs() as $photoLang){
                             if ($photoLang->getPhoLangIdLang()->getLangCode() == 'ES')
                                 $photoLang->setPhoLangDescription($desc);
                             else{
                                 try{
                                 $response = $translator->translate($desc, 'ES', $photoLang->getPhoLangIdLang()->getLangCode());
                                 if ($response->getCode() == TranslatorResponseStatusCode::STATUS_200)
                                     $photoLang->setPhoLangDescription($response->getTranslation());
                                 else $photoLang->setPhoLangDescription($desc);
                                 }
                                 catch (\Exception $exc){
                                     $photoLang->setPhoLangDescription($desc);
                                 }
                             }
                             $em->persist($photoLang);
                         }
                        }
                        else{

                            foreach ($language as $lang){
                                $photoLang = new photoLang();
                                if ($lang->getLangCode() == 'ES')
                                $photoLang->setPhoLangDescription($desc);
                                else{
                                    $response = $translator->translate($desc, 'ES', $lang->getLangCode());
                                    if ($response->getCode() == TranslatorResponseStatusCode::STATUS_200)
                                        $photoLang->setPhoLangDescription($response->getTranslation());
                                    else $photoLang->setPhoLangDescription($desc);
                                }
                                $photoLang->setPhoLangIdLang($lang);
                                $photoLang->setPhoLangIdPhoto($photo);
                                $em->persist($photoLang);
                            }
                        }
                    }
                }
//          }
//        catch (\Exception $exc){
//            return new JsonResponse([
//                'success' => false,
//                'message'=>$exc->getMessage()
//            ]);
//        }
            }
           $em->flush();
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
    public function getContentTabStep4Action(Request $request)
    {
        return new JsonResponse([
            'num' => $request->get('num'),
            'success' => true,
            'html' => $this->renderView('MyCpCasaModuleBundle:form:form4.html.twig', array('num' => $request->get('num'))),
            'msg' => 'Nueva habitacion']);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="casa_publish_ownership", path="/ownership/publish")
     */
    public function publishOwnershipAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ownership = $this->getUser()->getUserUserCasa()[0]->getUserCasaOwnership();
        $templatingService = $this->container->get('templating');
        $emailSubject='Proceso de registro de propiedad completado';
        $localOperationAssistant = $em->getRepository("mycpBundle:localOperationAssistant")->findOneBy(array("municipality" => $ownership->getOwnAddressMunicipality()->getMunId(), "active" => 1));

        $body = $templatingService->renderResponse('MyCpCasaModuleBundle:mail:publishedOwnership.html.twig', array('ownership'=>$ownership, 'user_locale' => "es",'user_full_name'=>$this->getUser()->getUserUserName().' '.$this->getUser()->getUserLastName()));
//        $emailService->sendEmail(array($this->getUser()->getUsername()), $emailSubject, $body);
        $service_email = $this->get('Email');
        $service_email->sendEmail($emailSubject, 'no_reply@mycasaparticular.com', 'MyCasaParticular.com', $this->getUser()->getUsername(), $body);
        return $this->render('MyCpCasaModuleBundle:Registration:published.html.twig', array(
            "code" => $ownership->getOwnMcpCode(),
            'assistant' => $localOperationAssistant
        ));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="casa_contact_services", path="/ownership/services/{id}")
     */
    public function contactForServicesAction(Request $request, $id)
    {   $services=array(
        '','Contenido profesional (70 CUC)', 'Fotografía profesional (160 CUC)','Video promocional (195 CUC)','Paquete Contenido profesional + Video promocional (235 CUC)',
        'Paquete Fotografía profesional + Video promocional (320 CUC)','Paquete Contenido profesional + Fotografía profesional + Video promocional (380 CUC)'
    );
        $em = $this->getDoctrine()->getManager();
        $ownership = $this->getUser()->getUserUserCasa()[0]->getUserCasaOwnership();
        $templatingService = $this->container->get('templating');
        $emailSubject='Solicitud de servicios profesionales';
        $body = $templatingService->renderResponse('MyCpCasaModuleBundle:mail:services-mail.html.twig', array('user'=>$this->getUser(), 'servicio'=>$services[$id]));
//        $emailService->sendEmail(array($this->getUser()->getUsername()), $emailSubject, $body);
        $service_email = $this->get('Email');
        $service_email->sendEmail($emailSubject, $this->getUser()->getUsername(), $this->getUser()->getUsername(), 'sales@mycasaparticular.com', $body);
//        $service_email->sendEmail($emailSubject, 'no_reply@mycasaparticular.com', 'MyCasaParticular.com', $this->getUser()->getUsername(), $body);
        return $this->render('MyCpCasaModuleBundle:Registration:services_success.html.twig', array());
    }

    /**
     * Para enviar el correo que se desactivo o elimino una habitacion
     * @return bool
     */
    function submitEmailReservationTeamRoom($room,$ownership,$reserved){
        $emailService = $this->container->get('mycp.service.email_manager');
        $templatingService = $this->container->get('templating');
        $emailSubject='Desactivar una habitación reservada';
        $body = $templatingService->renderResponse('MyCpCasaModuleBundle:mail:deleteRoom.html.twig', array('room'=>$room,'ownership'=>$ownership,'user_locale'=>'es','reserveds'=>$reserved));
        $emailService->sendEmail(array('reservation@mycasaparticular.com'), $emailSubject, $body);
        return true;
    }
    /**
     * Para enviar el correo que se desactivo una propiedad
     * @return bool
     */
    function submitEmailReservationTeamProperty($ownership,$reserved){
        $emailService = $this->container->get('mycp.service.email_manager');
        $templatingService = $this->container->get('templating');
        $emailSubject='Desactivar una propiedad reservada';
        $body = $templatingService->renderResponse('MyCpCasaModuleBundle:mail:deleteProperty.html.twig', array('ownership'=>$ownership,'user_locale'=>'es','reserveds'=>$reserved));
        $emailService->sendEmail(array('reservation@mycasaparticular.com'), $emailSubject, $body);
        return true;
    }
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="change_active_room", path="/change/active/room")
     */
    public function changeActiveRoomAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $room = $em->getRepository('mycpBundle:room')->find($request->get('idroom'));
        $ownership = $this->getUser()->getUserUserCasa()[0]->getUserCasaOwnership();
        $time = new \DateTime();
        $start= $time->format('Y-m-d H:i:s');
        $reserved = $em->getRepository('mycpBundle:ownershipReservation')->getReservationByRoomByStartDate($request->get('idroom'), $start);
        if(count($reserved) && $request->get('val')!='true'){
            if($request->get('forced')=='true'){
                $room->setRoomActive(($request->get('val') == 'false' ? 0 : 1));
                $em->persist($room);
                $em->flush();
                $em->getRepository('mycpBundle:ownership')->updateGeneralData($ownership);
                //Mando notificacion al equipo de reserva
                self::submitEmailReservationTeamRoom($room,$ownership,$reserved);
                $response=array( 'success' => true,'msg' => 'Se ha cambiado el estado');
            }
            else
                $response=array( 'success' => false);
        }
        else{
            $room->setRoomActive(($request->get('val') == 'false' ? 0 : 1));
            $em->persist($room);
            $em->flush();
            $response=array( 'success' => true,'msg' => 'Se ha cambiado el estado');
            $em->getRepository('mycpBundle:ownership')->updateGeneralData($ownership);
        }
        return new JsonResponse($response);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="delete_uploaded_photo", path="/photo/remove/{id}")
     */
    public function removeOwnPhotoAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        try {
            $em->getRepository('mycpBundle:ownershipPhoto')->deleteOwnPhoto($id, $this->get('service_container'));
            $ownership = $this->getUser()->getUserUserCasa()[0]->getUserCasaOwnership();
            $em->getRepository("mycpBundle:ownershipPhoto")->updatePrincipalPhoto($ownership);

        } catch (\Exception $exc) {
            return new JsonResponse([
                'success' => false,
                'message' => $exc->getMessage()
            ]);
        }
        return new JsonResponse('Ok');
    }
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="show_property", path="/datos")
     */
    public function showPropertyAction(Request $request)
    {
        $ownership = $this->getUser()->getUserUserCasa()[0]->getUserCasaOwnership();
        if($ownership->getOwnLangs()){
            if(substr($ownership->getOwnLangs(),0,1))
                $langs[]='1000';
            if(substr($ownership->getOwnLangs(),1,1))
                $langs[]='0100';
            if(substr($ownership->getOwnLangs(),2,1))
                $langs[]='0010';
            if(substr($ownership->getOwnLangs(),3,1))
                $langs[]='0001';
        }
        return $this->render('MyCpCasaModuleBundle:Steps:property.html.twig', array(
            'ownership'=>$ownership,
            'dashboard'=>true,
            'langs'=>(isset($langs))?$langs:array()
        ));
    }
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="show_description", path="/descripcion")
     */
    public function showDescriptionAction(Request $request)
    {
        $ownership = $this->getUser()->getUserUserCasa()[0]->getUserCasaOwnership();
        return $this->render('MyCpCasaModuleBundle:Steps:step3.html.twig', array(
            'ownership'=>$ownership,
            'dashboard'=>true
        ));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="save_description", path="/save/description")
     */
    public function saveDescriptionAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ownership = $em->getRepository('mycpBundle:ownership')->find($request->get('idown'));
        $description = $request->get('comment-one') . ' ' . $request->get('comment-two') . ' ' . $request->get('comment-three') . ' ' . $request->get('comment-four');

        $language = $em->getRepository('mycpBundle:lang')->findAll();
        $translator = $this->get("mycp.translator.service");

        $ownershipDescriptionLangs = $em->getRepository('mycpBundle:ownershipDescriptionLang')->findBy(array('odl_ownership' => $request->get('idown')));
        if(isset($ownershipDescriptionLangs[0]))
        {
            foreach($ownershipDescriptionLangs as $desc_lang)
                $em->remove($desc_lang);
        }
        foreach ($language as $lang) {
            $ownershipDescriptionLang = new ownershipDescriptionLang();
            if ($lang->getLangCode() == 'ES') {
                $ownershipDescriptionLang->setOdlOwnership($ownership)
                    ->setOdlIdLang($lang)//id del lenguage
                    ->setOdlBriefDescription($request->get('comment-one'))//descripcion corta que corresponde al primer parrafo
                    ->setOdlDescription($description)
                    ->setOdlAutomaticTranslation(0);
            } else {
                $response = $translator->translate($description, 'ES', $lang->getLangCode());
                if ($response->getCode() == TranslatorResponseStatusCode::STATUS_200)
                    $translatedDescription = $response->getTranslation();

                $response = $translator->translate($request->get('comment-one'), 'ES', $lang->getLangCode());
                if ($response->getCode() == TranslatorResponseStatusCode::STATUS_200)
                    $translatedBriefDescription = $response->getTranslation();


                $ownershipDescriptionLang->setOdlOwnership($ownership)
                    ->setOdlIdLang($lang)//id del lenguage
                    ->setOdlDescription($translatedDescription)
                    ->setOdlBriefDescription($translatedBriefDescription) //descripcion corta que corresponde al primer parrafo
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
    public function saveStep7Action(Request $request)
    {
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
        $homeownerName = $request->get('homeownerName');
        $email = $request->get('email');
        $publishAccommodation = ($request->get("publishAccommodation") == "true");
        $hasError = false;

        $em = $this->getDoctrine()->getManager();
        $accommodation = $em->getRepository('mycpBundle:ownership')->find($idAccommodation);

        $accommodation->setOwnMobileNumber($mobile)
            ->setOwnPhoneNumber($phone)
            ->setOwnEmail1($email)
            ->setOwnEmail2($email2)
            ->setOwnHomeowner1($homeownerName)
            ->setOwnHomeowner2($secondOwner);
        $em->persist($accommodation);

        $ownerAccommodation = $em->getRepository("mycpBundle:ownerAccommodation")->getMainOwner($idAccommodation);
        $owner = new owner();

        if ($ownerAccommodation != null) {
            $owner = $ownerAccommodation->getOwner();
        } else {
            $ownerAccommodation = new ownerAccommodation();
        }

        $municipality = $em->getRepository("mycpBundle:municipality")->find($municipalityId);
        $province = $em->getRepository("mycpBundle:province")->find($provinceId);

        $owner->setAddressBetween1($between1)
            ->setFullName($homeownerName)
            ->setAddressBetween2($between2)
            ->setPhone($phone)
            ->setMobile($mobile)
            ->setEmail($email)
            ->setEmail2($email2)
            ->setMunicipality($municipality)
            ->setProvince($province)
            ->setAddressMainStreet($mainStreet)
            ->setAddressStreetNumber($streetNumber)
            ->setMain(true)
        ;

        $em->persist($owner);

        $ownerAccommodation->setAccommodation($accommodation)
            ->setOwner($owner);

        $em->persist($ownerAccommodation);

        if ($request->get('dashboard')=="true" && $request->get("changePassword")=="true") {
            //die(dump($request));
            $password = $request->get('password');

                $factory = $this->get('security.encoder_factory');
                $user = $this->getUser();

                $encoder = $factory->getEncoder($user);
                $password = $encoder->encodePassword($password, $user->getSalt());
                $user->setUserPassword($password);


                $em->persist($user);
        }

        if($request->get("dashboard")=='false' && $publishAccommodation)
        {
            $canPublish = $em->getRepository("mycpBundle:ownership")->canActive($accommodation);

            if($canPublish) {
                //Preguntar si los datos primarios estan llenos
                $status = $em->getRepository("mycpBundle:ownershipStatus")->find(ownershipStatus::STATUS_ACTIVE);
                $accommodation->setOwnStatus($status)
                    ->setWaitingForRevision(true)
                    ->setOwnPublishDate(new \DateTime());
                $em->persist($accommodation);

                //Insertar un ownershipStatistics
                $statistic = new ownershipStatistics();
                $statistic->setAccommodation($accommodation)
                    ->setCreated(true)
                    ->setStatus($status)
                    ->setUser($this->getUser())
                    ->setNotes("Inserted in Casa Module");

                $em->persist($statistic);
            }
            else {
                $hasError = true;
            }
        }


        $em->flush();

        //Update general data
        $em->getRepository("mycpBundle:ownership")->updateGeneralData($accommodation);

        //Enviar correo
        if(!$request->get("dashboard") && $publishAccommodation)
        {
            $service_email = $this->get('Email');
            $service_email->sendInfoCasaRentaCommand($this->getUser());
        }

        if ($request->get('dashboard')) {
            return $this->render('MyCpCasaModuleBundle:Steps:step7.html.twig', array(
                'ownership' => $accommodation,
                'dashboard' => true
            ));
        }
        else {
            if($hasError)
                return new JsonResponse([
                    'success' => false,
                    'msg' => 'Para publicar su alojamiento debe tener fotos, una descripción y al menos una habitación.'
                ]);
            else
                return new JsonResponse([
                    'success' => true
                ]);
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="save_avatar", path="/save/avatar")
     */
    public function saveAvatarAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        //subir photo
        $dir = $this->container->getParameter('user.dir.photos');
        $file = $request->files->get('file');
        if (isset($file)) {
            $photo = new photo();
            $fileName = uniqid('user-') . '-photo.jpg';
            $file->move($dir, $fileName);
            //Redimensionando la foto del usuario
            \MyCp\mycpBundle\Helpers\Images::resize($dir . $fileName, 150);
            $photo->setPhoName($fileName);
            $user->setUserPhoto($photo);
            $em->persist($photo);
        }
        $em->persist($user);
        $em->flush();
        return new JsonResponse([
            'success' => true,
            'dir'=>$fileName
        ]);
    }


    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="get_room_calendar", path="/calendar/get-events")
     */
    public function getRoomCalendarEventsAction(Request $request)
    {
        $events = array();
        $em = $this->getDoctrine()->getManager();
        $room = $request->get('room');
        $start = $request->get('start');
        $end = $request->get('end');
        $unavailability = $em->getRepository('mycpBundle:unavailabilityDetails')->getRoomDetailsForCasaModuleCalendar($room, $start, $end);
        $noav = array();
        foreach ($unavailability as $unab) {
            $days = date_diff($unab->getUdToDate(), $unab->getUdFromDate());
            $fecha = $unab->getUdFromDate();
            for ($i = 0; $i <= $days->d; $i++) {

                $event = array(
                    'title' => 'No Disponibildad id' . $unab->getUdId(),
                    'start' => $fecha->format('Y-m-d'),
//              'end'=>$unab->getUdToDate()->format('Y-m-d') ,
                    'className' => 'circle-warning'
                );

                $noav[] = $event;
                $fecha->add(new \DateInterval('P01D'));
            }


        }
        $events = array_merge($events, $noav);
        $reserved = $em->getRepository('mycpBundle:ownershipReservation')->getReservationReservedByRoom($room, $start, $end);
        $reserv = array();
        foreach ($reserved as $res) {
            $days = date_diff($res['own_res_reservation_to_date'], $res['own_res_reservation_from_date']);
            $fecha = $res['own_res_reservation_from_date'];
            for ($i = 0; $i < $days->d; $i++) {

                $event = array(
                    'title' => 'Reserva CAS'.$res['gen_res_id'],
                    'start' => $fecha->format('Y-m-d'),
//              'end'=>$unab->getUdToDate()->format('Y-m-d') ,
                    'className' => 'circle-info'
                );

                $reserv[] = $event;
                $fecha->add(new \DateInterval('P01D'));
            }


        }
        $events = array_merge($events, $reserv);
        $cancelled = $em->getRepository('mycpBundle:ownershipReservation')->getReservationCancelledByRoom($room, $start, $end);
        $reserv = array();
        foreach ($cancelled as $res) {
            $days = date_diff($res['own_res_reservation_to_date'], $res['own_res_reservation_from_date']);
            $fecha = $res['own_res_reservation_from_date'];
            for ($i = 0; $i <= $days->d; $i++) {

                $event = array(
                    'title' => 'Cancelada id' . $res['own_res_id'],
                    'start' => $fecha->format('Y-m-d'),
//              'end'=>$unab->getUdToDate()->format('Y-m-d') ,
                    'className' => 'circle-danger'
                );

                $reserv[] = $event;
                $fecha->add(new \DateInterval('P01D'));
            }
        }
        $events = array_merge($events, $reserv);


        return new JsonResponse($events);
    }
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="show_facilities", path="/panel/facilities")
     */
    public function showFacilitiesAction(Request $request)
    {
        $ownership = $this->getUser()->getUserUserCasa()[0]->getUserCasaOwnership();
        return $this->render('MyCpCasaModuleBundle:Steps:step5.html.twig', array(
            'ownership'=>$ownership,
            'dashboard'=>true
        ));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="show_user_profile", path="/panel/profile")
     */
    public function showUserProfileAction(Request $request)
    {
        $ownership = $this->getUser()->getUserUserCasa()[0]->getUserCasaOwnership();
        return $this->render('MyCpCasaModuleBundle:Steps:profile.html.twig', array(
            'ownership' => $ownership,
            'dashboard' => true
        ));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="save_unabailability", path="/save/unabailability")
     */
    public function saveUnabailabilityAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $room=$request->get('room');
        $start=\DateTime::createFromFormat('d/m/Y',$request->get('date_from'));
        $end=\DateTime::createFromFormat('d/m/Y',$request->get('date_to'));
        $status=$request->get('status');
        $reserved = $em->getRepository('mycpBundle:ownershipReservation')->getReservationReservedByRoomCasaModule($room,$start->format('Y-m-d'), $end->format('Y-m-d'));
         if(count($reserved)>0){
            return new JsonResponse([
                'success' => false,
                'message'=>'No se puede modificar en ese período pues tiene reservaciones pagadas'
            ]);
        }
        $unavailability = $em->getRepository('mycpBundle:unavailabilityDetails')->getRoomDetailsForCasaModuleCalendar($room, $start->format('Y-m-d'), $end->format('Y-m-d'));
        foreach($unavailability as $item){
          if($item->getUdFromDate()>=$start&&$item->getUdToDate()<=$end){
              $em->remove($item);
          }
          else if($item->getUdToDate()<=$end){
              $item->setUdFromDate($start);
              $em->persist($item);
          }
            else{
                $item->setUdToDate($end);
                $em->persist($item);
            }
        }
        if($status==0){
          $room=$em->getRepository('mycpBundle:room')->find($room);
          $nu= new unavailabilityDetails();
          $nu->setRoom($room);
          $nu->setUdFromDate($start);
          $nu->setUdToDate($end);
          $nu->setUdReason('Por el propietario');
            $em->persist($nu);
        }

        $em->flush();
        return new JsonResponse([
            'success' => true
        ]);
    }
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="active_property", path="/active/property")
     */
    public function activePropertyAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ownership = $this->getUser()->getUserUserCasa()[0]->getUserCasaOwnership();
        if($request->get('active')=='true'){
            $canPublish = $em->getRepository("mycpBundle:ownership")->canActive($ownership);

            if($canPublish) {
                $status = $em->getRepository("mycpBundle:ownershipStatus")->find(ownershipStatus::STATUS_ACTIVE);
                $ownership->setOwnStatus($status);
                $em->persist($ownership);
                $em->flush();
                $response = array('success' => true, 'msg' => 'Se ha cambiado el estado');
            }
            else
                $response=array( 'success' => false,'msg' => 'Para publicar su alojamiento debe tener fotos, una descripción y al menos una habitación.');
        }
        else{
            $time = new \DateTime();
            $start= $time->format('Y-m-d H:i:s');
            //$start='2015-07-02';
            $reserved = $em->getRepository('mycpBundle:generalReservation')->getReservationsByIdAccommodationByDateFrom($ownership->getOwnId(), $start);
            if(count($reserved)){
                if($request->get('forced')=='true'){
                    $status = $em->getRepository("mycpBundle:ownershipStatus")->find(ownershipStatus::STATUS_INACTIVE);
                    $ownership->setOwnStatus($status);
                    $em->persist($ownership);
                    $em->flush();
                    //Mando notificacion al equipo de reserva
                    self::submitEmailReservationTeamProperty($ownership,$reserved);
                    $response=array( 'success' => true,'msg' => 'Se ha cambiado el estado');
                }
                else
                    $response=array( 'success' => false, "msg" => "La casa no puede desactivarse porque tiene reservas pagadas futuras.");
            }
            else{
                $status = $em->getRepository("mycpBundle:ownershipStatus")->find(ownershipStatus::STATUS_INACTIVE);
                $ownership->setOwnStatus($status);
                $em->persist($ownership);
                $em->flush();
                $response=array('success' => true);
            }
        }
        return new JsonResponse($response);
    }
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="delete_property", path="/delete/property")
     */
    public function deletePropertyAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ownership = $this->getUser()->getUserUserCasa()[0]->getUserCasaOwnership();
        $status = $em->getRepository("mycpBundle:ownershipStatus")->find(ownershipStatus::STATUS_DELETED);
        $ownership->setOwnStatus($status);
        $em->persist($ownership);
        $em->flush();
        $time = new \DateTime();
        $start= $time->format('Y-m-d H:i:s');
        $reserved = $em->getRepository('mycpBundle:generalReservation')->getReservationsByIdAccommodationByDateFrom($ownership->getOwnId(), $start);
        //Mando notificacion al equipo de reserva
        self::submitEmailReservationTeamProperty($ownership,$reserved);
        $response=array( 'success' => true,'msg' => 'Se ha cambiado el estado');
        return new JsonResponse($response);
    }
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="delete_room", path="/delete/room")
     */
    public function deleteRoomAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $room = $em->getRepository('mycpBundle:room')->find($request->get('idroom'));
        $ownership = $this->getUser()->getUserUserCasa()[0]->getUserCasaOwnership();
        $time = new \DateTime();
        $start= $time->format('Y-m-d H:i:s');
        $reserved = $em->getRepository('mycpBundle:ownershipReservation')->getReservationByRoomByStartDate($request->get('idroom'), $start);
        if(count($reserved)){
            $em->remove($room);
            $em->flush();
            $em->getRepository('mycpBundle:ownership')->updateGeneralData($ownership);
            //Mando notificacion al equipo de reserva
            self::submitEmailReservationTeamRoom($room,$ownership,$reserved);
            $response=array( 'success' => true,'msg' => 'El elemento se ha eliminado satisfactoriamente');
        }
        else{
            $em->remove($room);
            $em->flush();
            $response=array( 'success' => true,'msg' => 'El elemento se ha eliminado satisfactoriamente');
            $em->getRepository('mycpBundle:ownership')->updateGeneralData($ownership);
        }
        return new JsonResponse($response);
    }

}