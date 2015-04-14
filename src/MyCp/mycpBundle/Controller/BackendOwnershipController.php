<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\batchType;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\room;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;
use MyCp\mycpBundle\Entity\ownership;
use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Entity\photo;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Entity\photoLang;
use MyCp\mycpBundle\Entity\ownershipPhoto;
use MyCp\mycpBundle\Helpers\OwnershipStatuses;
use MyCp\mycpBundle\Entity\ownershipStatus;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use MyCp\mycpBundle\Helpers\UserMails;
use MyCp\mycpBundle\Helpers\Operations;

class BackendOwnershipController extends Controller {

    public function list_photosAction($id_ownership, $items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $data = array();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        $em = $this->getDoctrine()->getEntityManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $data['languages'] = $em->getRepository('mycpBundle:lang')->getAll();
        $photos = $paginator->paginate($em->getRepository('mycpBundle:ownershipPhoto')->getPhotosByIdOwnership($id_ownership))->getResult();
        foreach ($photos as $photo) {
            $data['description_photo_' . $photo->getOwnPhoPhoto()->getPhoId()] = $em->getRepository('mycpBundle:photoLang')->findBy(array('pho_lang_id_photo' => $photo->getOwnPhoPhoto()->getPhoId()));
        }
        $dir = $this->container->getParameter('ownership.dir.photos');
        $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);
        return $this->render('mycpBundle:ownership:photosList.html.twig', array(
                    'data' => $data,
                    'photos' => $photos,
                    'dir' => $dir,
                    'id_ownership' => $id_ownership,
                    'ownership' => $ownership,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems(),
        ));
    }

    public function new_photosAction($id_ownership, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $data = array();
        $post = '';
        $errors = array();
        $em = $this->getDoctrine()->getEntityManager();
        $data['languages'] = $em->getRepository('mycpBundle:lang')->getAll();
        $dir = $this->container->getParameter('ownership.dir.photos');
        $dir_thumbs = $this->container->getParameter('ownership.dir.thumbnails');
        $dir_watermark = $this->container->getParameter('dir.watermark');
        $photo_size = $this->container->getParameter('ownership.dir.photos.size');
        $thumbs_size = $this->container->getParameter('thumbnail.size');
        $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);

        if ($request->getMethod() == 'POST') {
            $post = $request->request->getIterator()->getArrayCopy();
            $files = $request->files->get('images');

            if ($files['files'][0] === null) {
                $data['error'] = 'Debe seleccionar una imagen.';
            } else {
                $count_errors = 0;
                foreach ($files['files'] as $file) {
                    if ($file->getClientMimeType() != 'image/jpeg' && $file->getClientMimeType() != 'image/gif' && $file->getClientMimeType() != 'image/png') {
//$file->getClientSize()< 102400
                        $data['error'] = 'Extensión de fichero no admitida.';
                        $count_errors++;
                        break;
                    }
                }

                $not_blank_validator = new NotBlank();
                $not_blank_validator->message = "Este campo no puede estar vacío.";
                $array_keys = array_keys($post);
                $count = $errors_validation = 0;
                foreach ($post as $item) {
                    $errors[$array_keys[$count]] = $errors_validation = $this->get('validator')->validateValue($item, $not_blank_validator);
                    $count_errors+=count($errors_validation);
                    $count++;
                }

                if ($count_errors == 0) {
                    $langs = $em->getRepository('mycpBundle:lang')->findAll();
                    foreach ($files['files'] as $file) {
                        $ownershipPhoto = new ownershipPhoto();
                        $photo = new photo();
                        $fileName = uniqid('ownership-') . '-photo.jpg';
                        $file->move($dir, $fileName);

//Creando thumbnail, redimensionando y colocando marca de agua
                        \MyCp\mycpBundle\Helpers\Images::createThumbnail($dir . $fileName, $dir_thumbs . $fileName, $thumbs_size);
                        \MyCp\mycpBundle\Helpers\Images::resizeAndWatermark($dir, $fileName, $dir_watermark, $photo_size, $this->container);

                        $photo->setPhoName($fileName);
                        $ownershipPhoto->setOwnPhoOwn($ownership);
                        $ownershipPhoto->setOwnPhoPhoto($photo);
                        $em->persist($ownershipPhoto);
                        $em->persist($photo);

                        foreach ($langs as $lang) {
                            $photoLang = new photoLang();
                            $photoLang->setPhoLangDescription($post['description_' . $lang->getLangId()]);
                            $photoLang->setPhoLangIdLang($lang);
                            $photoLang->setPhoLangIdPhoto($photo);
                            $em->persist($photoLang);
                        }
                    }
                    $em->flush();
                    $message = 'Ficheros subidos satisfactoriamente.';
                    $this->get('session')->getFlashBag()->add('message_ok', $message);

                    $service_log = $this->get('log');
                    $service_log->saveLog('Create photo, entity ' . $ownership->getOwnName(), BackendModuleName::MODULE_OWNERSHIP);

                    switch($request->get('save_operation'))
                    {
                        case Operations::SAVE_AND_EXIT:
                            return $this->redirect($this->generateUrl('mycp_list_photos_ownership', array('id_ownership' => $id_ownership)));
                        case Operations::SAVE_AND_NEW:
                            return $this->redirect($this->generateUrl('mycp_new_photos_ownership', array('id_ownership' => $id_ownership)));
                        case Operations::SAVE_AND_PUBLISH_ACCOMMODATION:
                            return $this->redirect($this->generateUrl('mycp_publish_ownership', array('idOwnership' => $id_ownership)));

                    }

                }
            }
        }
        return $this->render('mycpBundle:ownership:photosNew.html.twig', array(
                    'data' => $data,
                    'dir' => $dir,
                    'id_ownership' => $id_ownership,
                    'ownership' => $ownership,
                    'errors' => $errors,
                    'post' => $post));
    }

    public function list_ownershipsAction($items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $page = 1;
        $filter_active = $request->get('filter_active');

        $filter_province = $request->get('filter_province');

        $filter_destination = $request->get('filter_destination');
        $filter_name = $request->get('filter_name');
        $filter_municipality = $request->get('filter_municipality');
        $filter_type = $request->get('filter_type');
        $filter_category = $request->get('filter_category');
        $filter_code = $request->get('filter_code');
        $filter_saler = $request->get('filter_saler');
        $filter_visit_date = $request->get('filter_visit_date');
        $filter_other = $request->get('filter_other');
        if ($request->getMethod() == 'POST' && $filter_name == 'null' && $filter_active == 'null' && $filter_province == 'null' && $filter_municipality == 'null' &&
                $filter_type == 'null' && $filter_category == 'null' && $filter_code == 'null' && $filter_saler == 'null' && $filter_visit_date == 'null' && $filter_destination == 'null' && $filter_other == 'null'
        ) {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_ownerships'));
        }

        if ($filter_code == 'null')
            $filter_code = '';
        if ($filter_name == 'null')
            $filter_name = '';
        if ($filter_saler == 'null')
            $filter_saler = '';
        if ($filter_visit_date == 'null')
            $filter_visit_date = '';
        if ($filter_destination == 'null')
            $filter_destination = '';
        if ($filter_other == 'null')
            $filter_other = '';
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $em = $this->getDoctrine()->getEntityManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $ownerships = $paginator->paginate($em->getRepository('mycpBundle:ownership')->getAll(
                                $filter_code, $filter_active, $filter_category, $filter_province, $filter_municipality, $filter_destination, $filter_type, $filter_name, $filter_saler, $filter_visit_date, $filter_other
                ))->getResult();
        /* $data = array();
          foreach ($ownerships as $ownership) {
          $photos = $em->getRepository('mycpBundle:ownershipPhoto')->findBy(array('own_pho_own' => $ownership->getOwnId()));
          $data[$ownership->getOwnId() . '_photo_count'] = count($photos);
          } */

        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_OWNERSHIP);
        return $this->render('mycpBundle:ownership:list.html.twig', array(
                    'ownerships' => $ownerships,
                    //'photo_count' => $data,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems(),
                    'filter_code' => $filter_code,
                    'filter_name' => $filter_name,
                    'filter_active' => $filter_active,
                    'filter_category' => $filter_category,
                    'filter_province' => $filter_province,
                    'filter_municipality' => $filter_municipality,
                    'filter_type' => $filter_type,
                    'filter_saler' => $filter_saler,
                    'filter_visit_date' => $filter_visit_date,
                    'filter_destination' => $filter_destination,
                    'filter_other' => $filter_other
        ));
    }

    public function delete_photoAction($id_ownership, $id_photo) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $em->getRepository("mycpBundle:ownershipPhoto")->deleteOwnPhoto($id_photo, $this->container);
        $em->getRepository("mycpBundle:ownershipPhoto")->checkOwnershipToInactivate($id_ownership);
        $message = 'El fichero se ha eliminado satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);
        $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);

        $service_log = $this->get('log');
        $service_log->saveLog('Delete photo, entity ' . $ownership->getOwnName(), BackendModuleName::MODULE_OWNERSHIP);

        return $this->redirect($this->generateUrl('mycp_list_photos_ownership', array('id_ownership' => $id_ownership)));
    }

    public function activeRoomAction($id_room, $activate) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();

        $em = $this->getDoctrine()->getManager();

        $ro = $em->getRepository('mycpBundle:room')->find($id_room);
        $id_ownership = $ro->getRoomOwnership()->getOwnId();

        $ro->setRoomActive($activate);
        $em->persist($ro);
        $em->flush();

        $own = $ro->getRoomOwnership();
        $rooms = $em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $own->getOwnId()));
        $count = count($rooms);
        $count_active = 0;
        $maximum_guests = 0;
        foreach ($rooms as $room) {
            if (!$room->getRoomActive())
                $count--;
            else {
                $count_active++;
                $maximum_guests += $room->getMaximumNumberGuests();
            }
        }

        $own->setOwnMaximumNumberGuests($maximum_guests);
        $own->setOwnRoomsTotal($count_active);
        if ($count <= 0) {
            $status = $em->getRepository('mycpBundle:ownershipstatus')->find(ownershipStatus::STATUS_INACTIVE);
            $own->setOwnStatus($status);
            $em->persist($own);
        }

        $em->flush();
        return $this->redirect($this->generateUrl('mycp_edit_ownership', array('id_ownership' => $id_ownership)));
    }

    public function batchProcessAction($items_per_page, Request $request)
    {
        /*$service_security = $this->get('Secure');
        $service_security->verifyAccess();*/
        $page = 1;
        $filter_status = $request->get('filter_status');
        $filter_start_date = $request->get('filter_start_date');

        if ($request->getMethod() == 'POST' && $filter_status == 'null' && $filter_start_date == 'null') {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_batch_process_ownership'));
        }

        if ($filter_status == 'null')
            $filter_status = '';
        if ($filter_start_date == 'null')
            $filter_start_date = '';
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $em = $this->getDoctrine()->getEntityManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $batchList = $paginator->paginate($em->getRepository('mycpBundle:batchProcess')->getAllByType(batchType::BATCH_TYPE_ACCOMMODATION,
            $filter_status, $filter_start_date))->getResult();

        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_OWNERSHIP);
        return $this->render('mycpBundle:ownership:batchProcessList.html.twig', array(
            'batchList' => $batchList,
            'items_per_page' => $items_per_page,
            'current_page' => $page,
            'total_items' => $paginator->getTotalItems(),
            'filter_status' => $filter_status,
            'filter_start_date' => $filter_start_date

        ));
    }

    public function edit_ownershipAction($id_ownership, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $ownership = new ownership();
        $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);
        $languages = $em->getRepository('mycpBundle:lang')->getAll();
        //$ownershipGeneralLangs = $em->getRepository('mycpBundle:ownershipGeneralLang')->findBy(array('ogl_ownership' => $id_ownership));
        $ownershipDescriptionLangs = $em->getRepository('mycpBundle:ownershipDescriptionLang')->findBy(array('odl_ownership' => $id_ownership));
        $ownershipKeywordsLangs = $em->getRepository('mycpBundle:ownershipKeywordLang')->findBy(array('okl_ownership' => $id_ownership));
        $errors = array();
        $data = array();
        $data['count_errors'] = 0;
        $rooms = $em->getRepository('mycpBundle:room')->findby(array('room_ownership' => $id_ownership));
        $count_rooms = count($rooms);

        $post['ownership_id'] = $ownership->getOwnId();
        $post['ownership_name'] = $ownership->getOwnName();
        $post['ownership_licence_number'] = $ownership->getOwnLicenceNumber();
        $post['ownership_mcp_code'] = $ownership->getOwnMcpCode();
        $post['ownership_address_street'] = $ownership->getOwnAddressStreet();
        $post['ownership_address_number'] = $ownership->getOwnAddressNumber();
        $post['ownership_address_between_street_1'] = $ownership->getOwnAddressBetweenStreet1();
        $post['ownership_address_between_street_2'] = $ownership->getOwnAddressBetweenStreet2();
        $post['ownership_address_province'] = $ownership->getOwnAddressProvince()->getProvId();
        $post['ownership_address_municipality'] = $ownership->getOwnAddressMunicipality()->getMunId();
        $post['ownership_mobile_number'] = $ownership->getOwnMobileNumber();
        $post['ownership_homeowner_1'] = $ownership->getOwnHomeowner1();
        $post['ownership_homeowner_2'] = $ownership->getOwnHomeowner2();
        $post['ownership_phone_code'] = "(+53) ". $ownership->getOwnAddressProvince()->getProvPhoneCode();
        $post['ownership_phone_number'] = $ownership->getOwnPhoneNumber();
        $post['ownership_email_1'] = $ownership->getOwnEmail1();
        $post['ownership_email_2'] = $ownership->getOwnEmail2();
        $post['ownership_category'] = $ownership->getOwnCategory();
        $post['ownership_type'] = $ownership->getOwnType();
        $post['comment'] = $ownership->getOwnComment();
        $post['ownership_percent_commission'] = $ownership->getOwnCommissionPercent();
        $post['ownership_saler'] = $ownership->getOwnSaler();
        $post['ownership_visit_date'] = $ownership->getOwnVisitDate();
        $post['ownership_creation_date'] = $ownership->getOwnCreationDate();
        $post['ownership_last_update'] = $ownership->getOwnLastUpdate();
        $post['ownership_publish_date'] = $ownership->getOwnPublishDate();

        $data['ownership_owner'] = ($ownership->getOwnOwnerPhoto() != null) ? $ownership->getOwnOwnerPhoto()->getPhoName() : "no_photo.gif";
        $data['ownership_visit_date'] = $ownership->getOwnVisitDate();
        $data['ownership_creation_date'] = $ownership->getOwnCreationDate();
        $data['ownership_last_update'] = $ownership->getOwnLastUpdate();
        $data['ownership_publish_date'] = $ownership->getOwnPublishDate();
        $post['ownership_destination'] = 0;

        $users_owner = $em->getRepository('mycpBundle:userCasa')->findBy(array('user_casa_ownership' => $id_ownership));

        if ($ownership->getOwnDestination() != null)
            $post['ownership_destination'] = $ownership->getOwnDestination()->getDesId();

        $langs = $ownership->getOwnLangs();
        if ($langs[0] == 1)
            $post['ownership_english_lang'] = 1;
        if ($langs[1] == 1)
            $post['ownership_french_lang'] = 1;
        if ($langs[2] == 1)
            $post['ownership_german_lang'] = 1;
        if ($langs[3] == 1)
            $post['ownership_italian_lang'] = 1;

        $post['facilities_breakfast'] = $ownership->getOwnFacilitiesBreakfast();
        $post['facilities_breakfast_price'] = $ownership->getOwnFacilitiesBreakfastPrice();
        $post['facilities_dinner'] = $ownership->getOwnFacilitiesDinner();
        $post['facilities_dinner_price_from'] = $ownership->getOwnFacilitiesDinnerPriceFrom();
        $post['facilities_dinner_price_to'] = $ownership->getOwnFacilitiesDinnerPriceTo();
        $post['facilities_parking'] = $ownership->getOwnFacilitiesParking();
        $post['facilities_parking_price'] = $ownership->getOwnFacilitiesParkingPrice();
        $post['facilities_notes'] = $ownership->getOwnFacilitiesNotes();
        $post['description_bicycle_parking'] = $ownership->getOwnDescriptionBicycleParking();
        $post['description_pets'] = $ownership->getOwnDescriptionPets();
        $post['description_laundry'] = $ownership->getOwnDescriptionLaundry();
        $post['description_internet'] = $ownership->getOwnDescriptionInternet();
        $post['geolocate_x'] = $ownership->getOwnGeolocateX();
        $post['geolocate_y'] = $ownership->getOwnGeolocateY();
        $post['top_20'] = $ownership->getOwnTop20();
        $data['top_20'] = $ownership->getOwnTop20();
        $post['not_recommendable'] = $ownership->getOwnNotRecommendable();
        $data['not_recommendable'] = $ownership->getOwnNotRecommendable();
        $post['selection'] = $ownership->getOwnSelection();
        $data['selection'] = $ownership->getOwnSelection();
        $post['inmediate_booking'] = $ownership->getOwnInmediateBooking();
        $data['inmediate_booking'] = $ownership->getOwnInmediateBooking();
        $data['country_code'] = $ownership->getOwnAddressProvince()->getProvId();
        $data['municipality_code'] = $ownership->getOwnAddressMunicipality()->getMunId();

        $post['status'] = ($ownership->getOwnStatus() != null) ? $ownership->getOwnStatus()->getStatusId() : null;
        $data['status_id'] = $post['status'];
        $data['status_name'] = ($ownership->getOwnStatus() != null) ? $ownership->getOwnStatus()->getStatusName() : null;

        $post['top_20'] = ($post['top_20'] == false) ? 0 : 1;
        $post['selection'] = ($post['selection'] == false) ? 0 : 1;
        $post['inmediate_booking'] = ($post['inmediate_booking'] == false) ? 0 : 1;
        $post['not_recommendable'] = ($post['not_recommendable'] == false) ? 0 : 1;
        $post['facilities_breakfast'] = ($post['facilities_breakfast'] == false) ? 0 : 1;
        $post['facilities_dinner'] = ($post['facilities_dinner'] == false) ? 0 : 1;
        $post['facilities_parking'] = ($post['facilities_parking'] == false) ? 0 : 1;
        $post['water_sauna'] = $ownership->getOwnWaterSauna();
        $post['water_jacuzee'] = $ownership->getOwnWaterJacuzee();
        $post['water_piscina'] = $ownership->getOwnWaterPiscina();

        foreach ($ownershipDescriptionLangs as $ownershipDescriptionLang) {
            $post['description_id_' . $ownershipDescriptionLang->getOdlIdLang()->getLangId()] = $ownershipDescriptionLang->getOdlId();
            $post['description_desc_' . $ownershipDescriptionLang->getOdlIdLang()->getLangId()] = $ownershipDescriptionLang->getOdlDescription();
            $post['description_brief_desc_' . $ownershipDescriptionLang->getOdlIdLang()->getLangId()] = $ownershipDescriptionLang->getOdlBriefDescription();
        }

        foreach ($ownershipKeywordsLangs as $ownershipKeywordsLang) {
            $post['kw_id_' . $ownershipKeywordsLang->getOklIdLang()->getLangId()] = $ownershipKeywordsLang->getOklId();
            $post['keywords_' . $ownershipKeywordsLang->getOklIdLang()->getLangId()] = $ownershipKeywordsLang->getOklKeywords();
        }

        for ($a = 1; $a <= $count_rooms; $a++) {
            $post['room_id_' . $a] = $rooms[$a - 1]->getRoomId();
            $post['room_type_' . $a] = $rooms[$a - 1]->getRoomType();
            $post['room_beds_number_' . $a] = $rooms[$a - 1]->getRoomBeds();
            //$post['room_price_up_from_' . $a] = $rooms[$a - 1]->getRoomPriceUpFrom();
            $post['room_price_up_to_' . $a] = $rooms[$a - 1]->getRoomPriceUpTo();
            //$post['room_price_down_from_' . $a] = $rooms[$a - 1]->getRoomPriceDownFrom();
            $post['room_price_down_to_' . $a] = $rooms[$a - 1]->getRoomPriceDownTo();
            $post['room_price_special_' . $a] = $rooms[$a - 1]->getRoomPriceSpecial();
            $post['room_climate_' . $a] = $rooms[$a - 1]->getRoomClimate();
            $post['room_audiovisual_' . $a] = $rooms[$a - 1]->getRoomAudiovisual();
            $post['room_smoker_' . $a] = $rooms[$a - 1]->getRoomSmoker();
            $post['room_safe_box_' . $a] = $rooms[$a - 1]->getRoomSafe();
            $post['room_baby_' . $a] = $rooms[$a - 1]->getRoomBaby();
            $post['room_bathroom_' . $a] = $rooms[$a - 1]->getRoomBathroom();
            $post['room_stereo_' . $a] = $rooms[$a - 1]->getRoomStereo();
            $post['room_windows_' . $a] = $rooms[$a - 1]->getRoomWindows();
            $post['room_balcony_' . $a] = $rooms[$a - 1]->getRoomBalcony();
            $post['room_terrace_' . $a] = $rooms[$a - 1]->getRoomTerrace();
            $post['room_yard_' . $a] = $rooms[$a - 1]->getRoomYard();
            $post['room_id_' . $a] = $rooms[$a - 1]->getRoomId();
            $post['room_ical_url_' . $a] = $rooms[$a - 1]->getICalUrl($this);

            $reservation = new ownershipReservation();

            $reservation = $em->getRepository('mycpBundle:ownershipReservation')->findOneBy(array('own_res_selected_room_id' => $rooms[$a - 1]->getRoomId()));

            $post['room_delete_' . $a] = $reservation ? 0 : 1;
            $post['room_active_' . $a] = $rooms[$a - 1]->getRoomActive();

            if ($post['room_terrace_' . $a] == true)
                $post['room_terrace_' . $a] = 1;
            if ($post['room_yard_' . $a] == true)
                $post['room_yard_' . $a] = 1;
            if ($post['room_stereo_' . $a] == true)
                $post['room_stereo_' . $a] = 1;
            if ($post['room_baby_' . $a] == true)
                $post['room_baby_' . $a] = 1;
            if ($post['room_safe_box_' . $a] == true)
                $post['room_safe_box_' . $a] = 1;
            if ($post['room_smoker_' . $a] == true)
                $post['room_smoker_' . $a] = 1;
            if ($post['room_active_' . $a] == true)
                $post['room_active_' . $a] = 1;
        }

        $post['edit_ownership'] = TRUE;
        $post['name_ownership'] = $ownership->getOwnName();
        $data['edit_ownership'] = TRUE;
        $data['id_ownership'] = $id_ownership;
        $data['name_ownership'] = $ownership->getOwnName();
        return $this->render('mycpBundle:ownership:new.html.twig', array('languages' => $languages, 'count_rooms' => $count_rooms, 'post' => $post, 'data' => $data, 'errors' => $errors, 'users' => $users_owner, 'total_users' => count($users_owner)));
    }

    public function delete_ownershipAction($id_ownership) {

        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();

        $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);
        $old_code = $ownership->getOwnMcpCode();

        $generalReservations = $em->getRepository('mycpBundle:generalReservation')->findBy(array('gen_res_own_id' => $id_ownership));

        if (count($generalReservations) == 0) {
            $ownershipReservations = $em->getRepository('mycpBundle:ownershipReservation')->getOwnResByOwnership($id_ownership);
            $ownershipGeneralLangs = $em->getRepository('mycpBundle:ownershipGeneralLang')->findBy(array('ogl_ownership' => $id_ownership));
            $ownershipDescLangs = $em->getRepository('mycpBundle:ownershipDescriptionLang')->findBy(array('odl_ownership' => $id_ownership));
            $ownershipKeywords = $em->getRepository('mycpBundle:ownershipKeywordLang')->findBy(array('okl_ownership' => $id_ownership));
            $ownershipRooms = $em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $id_ownership));
            $ownershipPhotos = $em->getRepository('mycpBundle:ownershipPhoto')->findBy(array('own_pho_own' => $id_ownership));
            $ownershipComments = $em->getRepository('mycpBundle:comment')->findBy(array('com_ownership' => $id_ownership));


            $dir = $this->container->getParameter('ownership.dir.photos');
            $dir_thumbs = $this->container->getParameter('ownership.dir.thumbnails');

            foreach ($ownershipComments as $ownershipComment) {
                $em->remove($ownershipComment);
            }

            foreach ($ownershipGeneralLangs as $ownershipgLang) {
                $em->remove($ownershipgLang);
            }

            foreach ($ownershipReservations as $ownershipReservation) {
                $em->remove($ownershipReservation);
            }

            foreach ($generalReservations as $generalReservation) {
                $em->remove($generalReservation);
            }

            foreach ($ownershipDescLangs as $ownershipDescLang) {
                $em->remove($ownershipDescLang);
            }

            foreach ($ownershipKeywords as $ownershipKeyword) {
                $em->remove($ownershipKeyword);
            }

            foreach ($ownershipRooms as $ownershipRoom) {
                $em->remove($ownershipRoom);
            }

            foreach ($ownershipPhotos as $ownershipPhoto) {
                $photo = $em->getRepository('mycpBundle:photo')->find($ownershipPhoto->getOwnPhoPhoto()->getPhoId());
                @unlink($dir . $photo->getPhoName());
                @unlink($dir_thumbs . $photo->getPhoName());
                $ownershipPhotoLangs = $em->getRepository('mycpBundle:photoLang')->findBy(array('pho_lang_id_photo' => $photo->getPhoId()));
                foreach ($ownershipPhotoLangs as $ownPhotoLang) {
                    $em->remove($ownPhotoLang);
                }

                $em->remove($ownershipPhoto);
                $em->remove($photo);
            }

            $em->remove($ownership);
            $em->flush();

            $message = 'Propiedad eliminada satisfactoriamente.';
            $this->get('session')->getFlashBag()->add('message_ok', $message);

            $service_log = $this->get('log');
            $service_log->saveLog('Delete entity ' . $old_code, BackendModuleName::MODULE_OWNERSHIP);
        } else {
            $status = $em->getRepository('mycpBundle:ownershipStatus')->find(ownershipStatus::STATUS_INACTIVE);
            $ownership->setOwnStatus($status);
            $em->persist($ownership);
            $em->flush();

            $message = 'Esta propiedad tiene reservaciones y no puede ser eliminada. En consecuencia, su estado ha sido cambiado a INACTIVO';
            $this->get('session')->getFlashBag()->add('message_ok', $message);

            $service_log = $this->get('log');
            $service_log->saveLog('Update entity status to INACTIVE. Property code ' . $old_code, BackendModuleName::MODULE_OWNERSHIP);
        }

        $userscasa = $em->getRepository('mycpBundle:userCasa')->findBy(array('user_casa_ownership' => $id_ownership));
        foreach ($userscasa as $usercasa) {
            $em->remove($usercasa);
        }
        $em->flush();

        return $this->redirect($this->generateUrl('mycp_list_ownerships'));
    }

    public function new_ownershipAction(Request $request) {

        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $count_rooms = 1;
        $post = $request->request->getIterator()->getArrayCopy();
        $errors = array();
        $data = array();
        $data['country_code'] = '';
        $data['municipality_code'] = '';
        $data['count_errors'] = 0;
        $data['status_id'] = 0;
        $data['ownership_owner'] = "no_photo.gif";
        $current_ownership_id = -1;

        if ($request->getMethod() == 'POST') {
            if ($request->request->get('new_room') == 1) {
                $count_rooms = $request->request->get('count_rooms') + 1;
                $data['new_room'] = TRUE;
                if (isset($data['ownership_visit_date']))
                    $data['ownership_visit_date'] = \MyCp\mycpBundle\Helpers\Dates::createFromString($post['ownership_visit_date'], '/', 1);
                if (isset($data['ownership_creation_date']))
                    $data['ownership_creation_date'] = \MyCp\mycpBundle\Helpers\Dates::createFromString($post['ownership_creation_date'], '/', 1);
                if (isset($data['ownership_last_update']))
                    $data['ownership_last_update'] = \MyCp\mycpBundle\Helpers\Dates::createFromString($post['ownership_last_update'], '/', 1);
                if (isset($data['ownership_publish_date']))
                    $data['ownership_publish_date'] = \MyCp\mycpBundle\Helpers\Dates::createFromString($post['ownership_publish_date'], '/', 1);
            } else {

                $not_blank_validator = new NotBlank();
                $not_blank_validator->message = "Este campo no puede estar vacío.";
                $emailConstraint = new Email();
                $emailConstraint->message = 'El email no es válido.';
                $length_validator = new Length(array('max' => 10, 'maxMessage' => 'Este campo no debe exceder 255 caracteres.'));
// mejoras
                $array_keys = array_keys($post);
                $count = $errors_validation = 0;
                $count_checkbox_lang = 0;
                foreach ($post as $item) {
                    if (strpos($array_keys[$count], 'ownership_') !== false) {
                        if ($array_keys[$count] != 'ownership_licence_number' &&
                                $array_keys[$count] != 'ownership_email_1' &&
                                $array_keys[$count] != 'ownership_address_between_street_1' &&
                                $array_keys[$count] != 'ownership_address_between_street_2' &&
                                $array_keys[$count] != 'ownership_mobile_number' &&
                                $array_keys[$count] != 'ownership_phone_number' &&
                                $array_keys[$count] != 'ownership_email_2' &&
                                $array_keys[$count] != 'ownership_homeowner_2' && $array_keys[$count] != 'ownership_saler' &&
                                $array_keys[$count] != 'ownership_visit_date' &&
                                $array_keys[$count] != 'ownership_destination' &&
                                $array_keys[$count] != 'user_create' &&
                                $array_keys[$count] != 'user_send_mail'
                        ) {
                            $errors[$array_keys[$count]] = $errors_validation = $this->get('validator')->validateValue($item, $not_blank_validator);
                            $data['count_errors']+=count($errors[$array_keys[$count]]);
                        }
                    }
                    if (strpos($array_keys[$count], 'room_') !== false &&
                            $array_keys[$count] != 'new_room') {
                        $errors[$array_keys[$count]] = $errors_validation = $this->get('validator')->validateValue($item, $not_blank_validator);
                        $data['count_errors']+=count($errors[$array_keys[$count]]);
                    }

//if action is new client casa
                    if (isset($post['user_name']) && !empty($post['user_name'])) {
                        if ($post['user_password'] != $post['user_re_password']) {
                            $errors['match_password'] = 'Este valor no es válido.';
                            $data['count_errors']+=1;
                        }
                        if (strlen($post['user_password']) < 6) {
                            $errors['match_password'] = 'Este valor es demasiado corto. Debería tener 6 caracteres o más.';
                            $data['count_errors']+=1;
                        }
                        $file = $request->files->get('user_photo');
//var_dump($file); exit();
                        if ($file && $file->getClientMimeType() != 'image/jpeg' && $file->getClientMimeType() != 'image/gif' && $file->getClientMimeType() != 'image/png') {
//$file->getClientSize()< 102400
                            $errors['user_photo'] = 'Extensión de fichero no admitida.';
                            $data['count_errors']+=1;
                        }

                        $user_db = $em->getRepository('mycpBundle:user')->findBy(array('user_name' => $post['user_name']));

                        if (count($user_db) != 0) {
                            $errors['user_name'] = 'Ya existe un usuario con ese nombre.';
                            $data['count_errors']+=1;
                        }
                    }

//Verificando que no existan otras propiedades con el mismo nombre
                    if (!array_key_exists('edit_ownership', $post)) {

                        $similar_names = $em->getRepository('mycpBundle:ownership')->findBy(array('own_name' => $post['ownership_name']));
                        if (count($similar_names) > 0) {
                            $errors['ownership_name'] = 'Ya existe una propiedad con este nombre. Por favor, introduzca un nombre similar o diferente.';
                            $data["count_errors"]+=1;
                        }
                    } else {
                        $own = $em->getRepository('mycpBundle:ownership')->find($post['edit_ownership']);

                        if (isset($own)) {
                            if ($own->getOwnName() != $post['ownership_name']) {
                                $similar_names = $em->getRepository('mycpBundle:ownership')->findBy(array('own_name' => $post['ownership_name']));
                                if (count($similar_names) > 0) {
                                    $errors['ownership_name'] = 'Ya existe una propiedad con este nombre. Por favor, introduzca un nombre similar o diferente.';
                                    $data["count_errors"]+=1;
                                }
                            }
                        }
                    }

//Verificando que no existan otras propiedades con el mismo código

                    if (!array_key_exists('edit_ownership', $post)) {

                        $similar = $em->getRepository('mycpBundle:ownership')->findBy(array('own_mcp_code' => $post['ownership_mcp_code']));
                        if (count($similar) > 0) {
                            $errors['ownership_mcp_code'] = 'Ya existe una propiedad con este código. Por favor, introduzca un código diferente.';
                            $data["count_errors"]+=1;
                        }
                    } else {
                        $own = $em->getRepository('mycpBundle:ownership')->find($post['edit_ownership']);

                        if (isset($own)) {
                            if ($own->getOwnMcpCode() != $post['ownership_mcp_code']) {
                                $similar = $em->getRepository('mycpBundle:ownership')->findBy(array('own_mcp_code' => $post['ownership_mcp_code']));
                                if (count($similar) > 0) {
                                    $errors['ownership_mcp_code'] = 'Ya existe una propiedad con este código. Por favor, introduzca un código diferente.';
                                    $data["count_errors"]+=1;
                                }
                            }
                        }
                    }
                    $count++;
                }

                $errors['ownership_email_1_email'] = $this->get('validator')->validateValue($post['ownership_email_1'], $emailConstraint);
                $errors['ownership_email_2_email'] = $this->get('validator')->validateValue($post['ownership_email_2'], $emailConstraint);
                $data['count_errors']+=count($errors['ownership_email_1_email']);
                $data['count_errors']+=count($errors['ownership_email_2_email']);

                if ($post['ownership_email_1'] != "" && !\MyCp\FrontEndBundle\Helpers\Utils::validateEmail($post['ownership_email_1'])) {
                    $errors['ownership_email_1_email'] = $emailConstraint->message;
                    $data['count_errors']++;
                    $count++;
                }

                if ($post['ownership_email_2'] != "" && !\MyCp\FrontEndBundle\Helpers\Utils::validateEmail($post['ownership_email_2'])) {
                    $errors['ownership_email_2_email'] = $emailConstraint->message;
                    $data['count_errors']++;
                    $count++;
                }
                $errors['facilities_breakfast'] = $this->get('validator')->validateValue($post['facilities_breakfast'], $not_blank_validator);
                $errors['status'] = $this->get('validator')->validateValue($post['status'], $not_blank_validator);
                $errors['facilities_dinner'] = $this->get('validator')->validateValue($post['facilities_dinner'], $not_blank_validator);
                $errors['facilities_parking'] = $this->get('validator')->validateValue($post['facilities_parking'], $not_blank_validator);
                $errors['geolocate_x'] = $this->get('validator')->validateValue($post['geolocate_x'], $not_blank_validator);
                $errors['geolocate_y'] = $this->get('validator')->validateValue($post['geolocate_y'], $not_blank_validator);

                $data['count_errors']+=count($errors['facilities_breakfast']);
                $data['count_errors']+=count($errors['facilities_dinner']);
                $data['count_errors']+=count($errors['facilities_parking']);
                $data['count_errors']+=count($errors['geolocate_x']);
                $data['count_errors']+=count($errors['geolocate_y']);

                $count_rooms = $request->request->get('count_rooms');
                $data['country_code'] = $post['ownership_address_province'];
                $data['municipality_code'] = $post['ownership_address_municipality'];

                if ($data['count_errors'] == 0) {
                    $dir = $this->container->getParameter('user.dir.photos');
                    $factory = $this->get('security.encoder_factory');

                    if ($request->request->get('edit_ownership')) {
                        $id_own = $request->request->get('edit_ownership');
                        if ($request->request->get('status') == ownershipStatus::STATUS_DELETED) {
                            return $this->redirect($this->generateUrl('mycp_delete_ownership', array('id_ownership' => $id_own)));
                        }
                        $service_log = $this->get('log');
                        $db_ownership = $em->getRepository('mycpBundle:ownership')->find($id_own);
                        $old_status = ($db_ownership->getOwnStatus() != null) ? $db_ownership->getOwnStatus()->getStatusId() : null; //$db_ownership->getOwnStatus()->getStatusId();
                        $new_status = $request->request->get('status');
                        $new_status_db = $em->getRepository('mycpBundle:ownershipStatus')->find($new_status)->getStatusName();
                        $any_edit = false;

                        if ($old_status != $new_status) {
                            $service_log->saveLog('Edit entity (Change status. From ' . (($db_ownership->getOwnStatus() != null) ? $db_ownership->getOwnStatus()->getStatusId() : 'Sin Estado') . ' to ' . $new_status_db . ' ) ' . $post['ownership_mcp_code'], BackendModuleName::MODULE_OWNERSHIP);
                            $any_edit = true;
                        }

                        $rooms_db = $em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $id_own));

                        $flag = 1;
                        $string_rooms_change_price = '';
                        if ($post['status'] == ownershipStatus::STATUS_ACTIVE)
                            foreach ($rooms_db as $room) {
                                /* $db_price_up_from = $room->getRoomPriceUpFrom();
                                  $post_price_up_from = $post['room_price_up_from_' . $flag]; */

                                $db_price_up_to = $room->getRoomPriceUpTo();
                                $post_price_up_to = $post['room_price_up_to_' . $flag];

                                /* $db_price_down_from = $room->getRoomPriceDownFrom();
                                  $post_price_down_from = $post['room_price_down_from_' . $flag]; */

                                $db_price_down_to = $room->getRoomPriceDownTo();
                                $post_price_down_to = $post['room_price_down_to_' . $flag];

                                if (isset($post['room_price_special_' . $flag])) {
                                    $db_price_special = $room->getRoomPriceSpecial();
                                    $post_price_special = $post['room_price_special_' . $flag];
                                }


                                /* if ($db_price_up_from != $post_price_up_from) {
                                  $string_rooms_change_price.=' Room ' . $flag . ' changed price (High season "FROM") from ' . $db_price_up_from . ' to ' . $post_price_up_from . '.';
                                  } */

                                if ($db_price_up_to != $post_price_up_to) {
                                    $string_rooms_change_price.=' Room ' . $flag . ' changed price (High season) from ' . $db_price_up_to . ' to ' . $post_price_up_to . '.';
                                }

                                /* if ($db_price_down_from != $post_price_down_from) {
                                  $string_rooms_change_price.=' Room ' . $flag . ' changed price (Low season "FROM") from ' . $db_price_down_from . ' to ' . $post_price_down_from . '.';
                                  } */

                                if ($db_price_down_to != $post_price_down_to) {
                                    $string_rooms_change_price.=' Room ' . $flag . ' changed price (Low season) from ' . $db_price_down_to . ' to ' . $post_price_down_to . '.';
                                }

                                if (isset($post['room_price_special_' . $flag]) && $db_price_special != $post_price_special) {
                                    $string_rooms_change_price.=' Room ' . $flag . ' changed price (Special season) from ' . $db_price_special . ' to ' . $post_price_special . '.';
                                }

                                $flag++;
                            }

                        if ($string_rooms_change_price != '') {
                            $service_log->saveLog('Edit entity. ' . $string_rooms_change_price . ' ' . $post['ownership_mcp_code'], BackendModuleName::MODULE_OWNERSHIP);
                            $any_edit = true;
                        }

                        $old_address_street = $db_ownership->getOwnAddressStreet();
                        $new_address_street = $request->request->get('ownership_address_street');

                        $old_number = $db_ownership->getOwnAddressNumber();
                        $new_number = $request->request->get('ownership_address_number');

                        $old_between_street_1 = $db_ownership->getOwnAddressBetweenStreet1();
                        $new_between_street_1 = $request->request->get('ownership_address_between_street_1');

                        $old_between_street_2 = $db_ownership->getOwnAddressBetweenStreet2();
                        $new_between_street_2 = $request->request->get('ownership_address_between_street_2');

                        if ($old_address_street != $new_address_street || $old_number != $new_number || $old_between_street_1 != $new_between_street_1 || $old_between_street_2 != $new_between_street_2) {
                            $any_edit = true;
                            $service_log->saveLog('Edit entity. Change address from ' . $old_address_street . ' street #' . $old_number . ' between ' . $old_between_street_1 . ' and ' . $old_between_street_2 .
                                    ' to ' . $new_address_street . ' street #' . $new_number . ' between ' . $new_between_street_1 . ' and ' . $new_between_street_2, BackendModuleName::MODULE_OWNERSHIP);
                        }

                        $old_phone_number = $db_ownership->getOwnPhoneNumber();
                        $new_phone_number = $request->request->get('ownership_phone_number');

                        $old_phone_code = $db_ownership->getOwnPhoneCode();
                        $new_phone_code = $request->request->get('ownership_phone_code');

                        if ($old_phone_number != $new_phone_number OR $old_phone_code != $new_phone_code) {
                            $any_edit = true;
                            $service_log->saveLog('Edit entity. Change phone number from ' . $old_phone_code . ' ' . $old_phone_number . ' to '
                                    . $new_phone_code . ' ' . $new_phone_number, BackendModuleName::MODULE_OWNERSHIP);
                        }

                        if ($any_edit == false) {
                            $service_log->saveLog('Edit entity ' . $post['ownership_mcp_code'], BackendModuleName::MODULE_OWNERSHIP);
                        }

                        $current_ownership_id = $em->getRepository('mycpBundle:ownership')->edit($post, $request, $dir, $factory, (isset($post['user_create']) && !empty($post['user_create'])), (isset($post['user_send_mail']) && !empty($post['user_send_mail'])), $this, $this->container->getParameter('user.dir.photos'));

                        //Enviar correo a los propietarios
                        if ($new_status == ownershipStatus::STATUS_ACTIVE && ($old_status == ownershipStatus::STATUS_IN_PROCESS or $old_status == ownershipStatus::STATUS_BATCH_PROCESS)) {
                            $id_ownership = $post['edit_ownership'];
                            $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);
                            $publishDate = $ownership->getOwnPublishDate();

                            if (!isset($publishDate) || $publishDate == null) {
                                $ownership->setOwnPublishDate(new \DateTime());
                                $em->persist($ownership);
                                $em->flush();

                                UserMails::sendOwnersMail($this, $post['ownership_email_1'], $post['ownership_email_2'], $post['ownership_homeowner_1'], $post['ownership_homeowner_2'], $post['ownership_name'], $post['ownership_mcp_code']);
                            }
                        }

                        $message = 'Propiedad actualizada satisfactoriamente.';
                    } else {

                        $current_ownership_id = $em->getRepository('mycpBundle:ownership')->insert($post, $request, $dir, $factory, (isset($post['user_create']) && !empty($post['user_create'])), (isset($post['user_send_mail']) && !empty($post['user_send_mail'])), $this, $this->container->getParameter('user.dir.photos'));
                        $message = 'Propiedad añadida satisfactoriamente.';
                        $service_log = $this->get('log');
                        $service_log->saveLog('Create entity ' . $post['ownership_mcp_code'], BackendModuleName::MODULE_OWNERSHIP);

                        //Enviar correo a los propietarios
                        if ($post['status'] == ownershipStatus::STATUS_ACTIVE)
                            UserMails::sendOwnersMail($this, $post['ownership_email_1'], $post['ownership_email_2'], $post['ownership_homeowner_1'], $post['ownership_homeowner_2'], $post['ownership_name'], $post['ownership_mcp_code']);
                    }
                    $this->get('session')->getFlashBag()->add('message_ok', $message);
                    if ($request->get('save_operation') == Operations::SAVE_AND_NEW) {
                        return $this->redirect($this->generateUrl('mycp_new_ownership'));
                    } else if ($request->get('save_operation') == Operations::SAVE_AND_ADD_PHOTOS) {
                        return $this->redirect($this->generateUrl('mycp_new_photos_ownership', array("id_ownership" => $current_ownership_id)));
                    } else {
                        return $this->redirect($this->generateUrl('mycp_list_ownerships'));
                    }
                }
            }
            if ($request->request->get('edit_ownership')) {
                $id_ownership = $request->request->get('edit_ownership');
                $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);
                $data['name_ownership'] = $ownership->getOwnName();
                $data['edit_ownership'] = $id_ownership;
                $data['id_ownership'] = $id_ownership;
                $data['status_id'] = $ownership->getOwnStatus()->getStatusId();
                $data['status_name'] = $ownership->getOwnStatus()->getStatusName();
                $data['top_20'] = $ownership->getOwnTop20();
                $data['not_recommendable'] = $ownership->getOwnNotRecommendable();
                $data['ownership_visit_date'] = $ownership->getOwnVisitDate();
                $data['ownership_creation_date'] = $ownership->getOwnCreationDate();
                $data['ownership_last_update'] = $ownership->getOwnLastUpdate();
                $data['ownership_publish_date'] = $ownership->getOwnPublishDate();
                $data['ownership_owner'] = ($ownership->getOwnOwnerPhoto() != null) ? $ownership->getOwnOwnerPhoto()->getPhoName() : "no_photo.gif";
            }
        }

        $errors_keys = array_keys($errors);
        $errors_temp = array();
        $flag = 0;



        foreach ($errors as $error) {
            if (is_object($error)) {
                if ($error->__toString() != '') {
                    array_push($errors_temp, $errors_keys[$flag]);
                }
            } else {
                array_push($errors_temp, $errors_keys[$flag]);
            }
            $flag++;
        }

        $errors_tab = array();
        foreach ($errors_temp as $error) {


            if (strpos($error, 'ownership') === 0) {
                $errors_tab['general_tab'] = true;
            }

            if (strpos($error, 'room') === 0) {
                $errors_tab['room_tab'] = true;
            }

            /* if(strpos($error,'facilities')===0)
              {
              $errors_tab['facilities_tab']=true;
              } */

            if (strpos($error, 'geolocate') === 0) {
                $errors_tab['house_tab'] = true;
            }

            if (strpos($error, 'status') === 0) {
                $errors_tab['house_tab'] = true;
            }

            if (strpos($error, 'match_password') === 0) {
                $errors_tab['user_tab'] = true;
            }
        }

        $languages = $em->getRepository('mycpBundle:lang')->getAll();
        return $this->render('mycpBundle:ownership:new.html.twig', array('languages' => $languages, 'count_rooms' => $count_rooms, 'post' => $post, 'data' => $data, 'errors' => $errors, 'errors_tab' => $errors_tab));
    }

    public function edit_photoAction($id_photo, $id_ownership, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);
        $post = '';
        $errors = array();
        $photo_langs = $em->getRepository('mycpBundle:photoLang')->findBy(array('pho_lang_id_photo' => $id_photo));
        $data = array();
        foreach ($photo_langs as $photo_lang) {
            $data['description_' . $photo_lang->getPhoLangIdLang()->getLangId()] = $photo_lang->getPhoLangDescription();
        }
        if ($request->getMethod() == 'POST') {
            $post = $request->request->getIterator()->getArrayCopy();
            $not_blank_validator = new NotBlank();
            $not_blank_validator->message = "Este campo no puede estar vacío.";
            $array_keys = array_keys($post);
            $count = $errors_validation = $count_errors = 0;
            foreach ($post as $item) {
                $errors[$array_keys[$count]] = $errors_validation = $this->get('validator')->validateValue($item, $not_blank_validator);
                $count_errors+=count($errors_validation);
                $count++;
            }
            if ($count_errors == 0) {
                foreach ($array_keys as $item) {
                    $id_lang = substr($item, 12, strlen($item));
                    $photo_lang = new \MyCp\mycpBundle\Entity\photoLang();
                    $photo_lang = $em->getRepository('mycpBundle:photoLang')->findBy(array('pho_lang_id_lang' => $id_lang, 'pho_lang_id_photo' => $id_photo));
                    if (isset($photo_lang[0])) {
                        $photo_lang[0]->setPhoLangDescription($post['description_' . $id_lang]);
                        $em->persist($photo_lang[0]);
                    }
                }
                $em->flush();
                $message = 'Imagen actualizada satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Edit photo, entity ' . $ownership->getOwnName(), BackendModuleName::MODULE_OWNERSHIP);

                return $this->redirect($this->generateUrl('mycp_list_photos_ownership', array('id_ownership' => $id_ownership)));
            }
        }
        $photo = $em->getRepository('mycpBundle:photo')->find($id_photo);
        $data['languages'] = $em->getRepository('mycpBundle:lang')->getAll();
        return $this->render('mycpBundle:ownership:photoEdit.html.twig', array(
                    'errors' => $errors,
                    'data' => $data,
                    'id_photo' => $id_photo,
                    'photo' => $photo,
                    'id_ownership' => $id_ownership,
                    'ownership' => $ownership,
                    'post' => $post));
    }

    public function send_ownershipAction($own_id) {
        $em = $this->getDoctrine()->getManager();
        $own = $em->getRepository('mycpBundle:ownership')->find($own_id);

        $own_mail_1 = $own->getOwnEmail1();
        $own_mail_2 = $own->getOwnEmail2();
        $owners_name_1 = $own->getOwnHomeowner1();
        $owners_name_2 = $own->getOwnHomeowner2();

        UserMails::sendOwnersMail($this, $own_mail_1, $own_mail_2, $owners_name_1, $owners_name_2, $own->getOwnName(), $own->getOwnMcpCode());

        return $this->redirect($this->generateUrl('mycp_edit_ownership', array('id_ownership' => $own_id)));
    }

    public function get_ownership_categoriesAction($post) {
        return $this->render('mycpBundle:utils:ownership_category.html.twig', array('data_post' => $post));
    }

    public function get_ownership_typesAction($post) {
        return $this->render('mycpBundle:utils:ownership_type.html.twig', array('data_post' => $post));
    }

    public function get_range_max_5Action($post, $id) {
        $selected = '';
        if (isset($post['room_beds_number_' . $id]))
            $selected = $post['room_beds_number_' . $id];
        return $this->render('mycpBundle:utils:range_max_5.html.twig', array('selected' => $selected));
    }

    public function get_climate_listAction($post, $id) {
        $selected = '';
        if (isset($post['room_climate_' . $id]))
            $selected = $post['room_climate_' . $id];
        return $this->render('mycpBundle:utils:ownership_climate.html.twig', array('selected' => $selected));
    }

    public function get_audiovisual_listAction($post, $id) {
        $selected = '';
        if (isset($post['room_audiovisual_' . $id]))
            $selected = $post['room_audiovisual_' . $id];
        return $this->render('mycpBundle:utils:ownership_audiovisual.html.twig', array('selected' => $selected));
    }

    public function get_bathroom_listAction($post, $id) {
        $selected = '';
        if (isset($post['room_bathroom_' . $id]))
            $selected = $post['room_bathroom_' . $id];
        return $this->render('mycpBundle:utils:ownership_bathroom.html.twig', array('selected' => $selected));
    }

    public function get_smoker_listAction($post, $id) {
        $selected = '';
        if (isset($post['room_smoker_' . $id]))
            $selected = $post['room_smoker_' . $id];
        return $this->render('mycpBundle:utils:yes_no.html.twig', array('selected' => $selected));
    }

    public function get_safe_box_listAction($post, $id) {
        $selected = '';
        if (isset($post['room_safe_box_' . $id]))
            $selected = $post['room_safe_box_' . $id];
        return $this->render('mycpBundle:utils:yes_no.html.twig', array('selected' => $selected));
    }

    public function get_baby_listAction($post, $id) {
        $selected = '';
        if (isset($post['room_baby_' . $id]))
            $selected = $post['room_baby_' . $id];
        return $this->render('mycpBundle:utils:yes_no.html.twig', array('selected' => $selected));
    }

    public function get_stereo_listAction($post, $id) {
        $selected = '';
        if (isset($post['room_stereo_' . $id]))
            $selected = $post['room_stereo_' . $id];
        return $this->render('mycpBundle:utils:yes_no.html.twig', array('selected' => $selected));
    }

    public function get_windows_listAction($post, $id) {
        $selected = '';
        if (isset($post['room_windows_' . $id]))
            $selected = $post['room_windows_' . $id];
        return $this->render('mycpBundle:utils:range_max_5.html.twig', array('selected' => $selected));
    }

    public function get_balcony_listAction($post, $id) {
        $selected = '';
        if (isset($post['room_balcony_' . $id]))
            $selected = $post['room_balcony_' . $id];
        return $this->render('mycpBundle:utils:range_max_5.html.twig', array('selected' => $selected));
    }

    public function get_terrace_listAction($post, $id) {
        $selected = '';
        if (isset($post['room_terrace_' . $id]))
            $selected = $post['room_terrace_' . $id];
        return $this->render('mycpBundle:utils:yes_no.html.twig', array('selected' => $selected));
    }

    public function get_yard_listAction($post, $id) {
        $selected = '';
        if (isset($post['room_yard_' . $id]))
            $selected = $post['room_yard_' . $id];
        return $this->render('mycpBundle:utils:yes_no.html.twig', array('selected' => $selected));
    }

    public function get_facilities_breakfast_listAction($post) {
        $selected = '';
        if (isset($post['facilities_breakfast']))
            $selected = $post['facilities_breakfast'];
        return $this->render('mycpBundle:utils:yes_no.html.twig', array('selected' => $selected));
    }

    public function get_facilities_dinner_listAction($post) {
        $selected = '';
        if (isset($post['facilities_dinner']))
            $selected = $post['facilities_dinner'];
        return $this->render('mycpBundle:utils:yes_no.html.twig', array('selected' => $selected));
    }

    public function get_facilities_parking_listAction($post) {
        $selected = '';
        if (isset($post['facilities_parking']))
            $selected = $post['facilities_parking'];
        return $this->render('mycpBundle:utils:yes_no.html.twig', array('selected' => $selected));
    }

    public function get_room_type_listAction($post, $id) {
        $selected = '';
        $not_blank = 0;
        if (isset($post['room_type_' . $id]))
            $selected = $post['room_type_' . $id];
        if (isset($post['service_room_type']))
            $selected = $post['service_room_type'];
        if (isset($post['not_blank']) && $post['not_blank'] == 1)
            $not_blank = 1;
        return $this->render('mycpBundle:utils:ownership_room_type.html.twig', array('selected' => $selected, 'not_blank' => $not_blank));
    }

    public function get_languagesAction($data) {
        $em = $this->getDoctrine()->getEntityManager();
        $languages = $em->getRepository('mycpBundle:lang')->findAll();
        return $this->render('mycpBundle:utils:ownership_languages.html.twig', array('languages' => $languages, 'data' => $data));
    }

    public function get_ownership_waterAction($data) {
        return $this->render('mycpBundle:utils:ownership_water.html.twig', array('data' => $data));
    }

    public function get_bicycle_listAction($post) {
        $selected = '';
        if (isset($post['description_bicycle_parking']))
            $selected = $post['description_bicycle_parking'];
        return $this->render('mycpBundle:utils:yes_no.html.twig', array('selected' => $selected));
    }

    public function get_pets_listAction($post) {
        $selected = '';
        if (isset($post['description_pets']))
            $selected = $post['description_pets'];
        return $this->render('mycpBundle:utils:yes_no.html.twig', array('selected' => $selected));
    }

    public function get_laundry_listAction($post) {
        $selected = '';
        if (isset($post['description_laundry']))
            $selected = $post['description_laundry'];
        return $this->render('mycpBundle:utils:yes_no.html.twig', array('selected' => $selected));
    }

    public function get_internet_listAction($post) {
        $selected = '';
        if (isset($post['description_internet']))
            $selected = $post['description_internet'];
        return $this->render('mycpBundle:utils:yes_no.html.twig', array('selected' => $selected));
    }

    public function get_ownerships_namesAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $ownerships = $em->getRepository('mycpBundle:ownership')->findAll();
        return $this->render('mycpBundle:utils:ownership_names.html.twig', array('ownerships' => $ownerships));
    }

    public function get_salers_namesAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $salers = $em->getRepository('mycpBundle:ownership')->getSalersNames();
        return $this->render('mycpBundle:utils:saler_names.html.twig', array('salers' => $salers));
    }

    public function get_statusAction($post) {

        $em = $this->getDoctrine()->getEntityManager();
        $selected = '';
        if (!is_array($post))
            $selected = $post;

        if (isset($post['status']))
            $selected = $post['status'];
        /* else
          $selected = ownershipStatus::STATUS_IN_PROCESS; */

        $status = $em->getRepository('mycpBundle:ownershipStatus')->findAll();
        return $this->render('mycpBundle:utils:ownership_status.html.twig', array('status' => $status, 'selected' => $selected, 'post' => $post));
    }

    public function publishAction($idOwnership) {
        $em = $this->getDoctrine()->getEntityManager();
        $ownership = $em->getRepository("mycpBundle:ownership")->find($idOwnership);

        $em->getRepository("mycpBundle:ownership")->publish($ownership);
        $email1 = $ownership->getOwnEmail1();
        $email2 = $ownership->getOwnEmail2();
        $owner1 = $ownership->getOwnHomeowner1();
        $owner2 = $ownership->getOwnHomeowner2();
        UserMails::sendOwnersMail($this, $email1, $email2, $owner1, $owner2, $ownership->getOwnName(), $ownership->getOwnMcpCode());

        $message = 'Propiedad publicada satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        return $this->redirect($this->generateUrl('mycp_list_photos_ownership', array("id_ownership" => $idOwnership)));
    }

    public function deleteMultiplesPhotosCallbackAction($idOwnership)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $photos_ids = $request->request->get('photos_ids');

        try {
        foreach($photos_ids as $photoId)
            $em->getRepository("mycpBundle:ownershipPhoto")->deleteOwnPhoto($photoId, $this->container);

        $em->getRepository("mycpBundle:ownershipPhoto")->checkOwnershipToInactivate($idOwnership);

        $message = 'Fotografías eliminadas satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        $response = $this->generateUrl('mycp_list_photos_ownership', array("id_ownership" => $idOwnership));
        } catch (\Exception $e) {
            $message = 'Las fotografías no pudieron ser eliminadas.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            $response = "ERROR";
        }
        return new Response($response);
    }

}
