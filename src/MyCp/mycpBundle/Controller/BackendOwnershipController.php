<?php

namespace MyCp\mycpBundle\Controller;

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
        $data['languages'] = $em->getRepository('mycpBundle:lang')->get_all_languages();
        $photos = $paginator->paginate($em->getRepository('mycpBundle:ownershipPhoto')->get_photos_by_id_ownership($id_ownership))->getResult();
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
        $data['languages'] = $em->getRepository('mycpBundle:lang')->get_all_languages();
        $dir = $this->container->getParameter('ownership.dir.photos');
        $dir_thumbs = $this->container->getParameter('ownership.dir.thumbnails');
        $dir_watermark = $this->container->getParameter('dir.watermark');
        $photo_size = $this->container->getParameter('ownership.dir.photos.size');
        $thumbs_size = $this->container->getParameter('thumbnail.size');

        if ($request->getMethod() == 'POST') {
            $post = $request->request->getIterator()->getArrayCopy();
            $files = $request->files->get('images');

            if ($files['files'][0] === null) {
                $data['error'] = 'Debe seleccionar una imágen.';
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
                    $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);
                    $langs = $em->getRepository('mycpBundle:lang')->findAll();
                    foreach ($files['files'] as $file) {
                        $ownershipPhoto = new ownershipPhoto();
                        $photo = new photo();
                        $fileName = uniqid('ownership-') . '-photo.jpg';
                        $file->move($dir, $fileName);

//Creando thumbnail, redimensionando y colocando marca de agua
                        \MyCp\mycpBundle\Helpers\Images::create_thumbnail($dir . $fileName, $dir_thumbs . $fileName, $thumbs_size);
                        \MyCp\mycpBundle\Helpers\Images::resize_and_watermark($dir . $fileName, $dir_watermark, $photo_size);

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
                    $service_log->save_log('Create photo, entity ' . $ownership->getOwnName(), 4);

                    return $this->redirect($this->generateUrl('mycp_list_photos_ownership', array('id_ownership' => $id_ownership)));
                }
            }
        }
        $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);
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
        $filter_name = $request->get('filter_name');
        $filter_municipality = $request->get('filter_municipality');
        $filter_type = $request->get('filter_type');
        $filter_category = $request->get('filter_category');
        $filter_code = $request->get('filter_code');
        $filter_saler = $request->get('filter_saler');
        $filter_visit_date = $request->get('filter_visit_date');
        if ($request->getMethod() == 'POST' && $filter_name == 'null' && $filter_active == 'null' && $filter_province == 'null' && $filter_municipality == 'null' &&
                $filter_type == 'null' && $filter_category == 'null' && $filter_code == 'null' && $filter_saler == 'null' && $filter_visit_date == 'null'
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

        if (isset($_GET['page']))
            $page = $_GET['page'];

        $em = $this->getDoctrine()->getEntityManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $ownerships = $paginator->paginate($em->getRepository('mycpBundle:ownership')->get_all_ownerships(
                                $filter_code, $filter_active, $filter_category, $filter_province, $filter_municipality, $filter_type, $filter_name, $filter_saler, $filter_visit_date
                ))->getResult();
        $data = array();
        foreach ($ownerships as $ownership) {
            $photos = $em->getRepository('mycpBundle:ownershipPhoto')->findBy(array('own_pho_own' => $ownership->getOwnId()));
            $data[$ownership->getOwnId() . '_photo_count'] = count($photos);
        }

        $service_log = $this->get('log');
        $service_log->save_log('Visit', 4);

        return $this->render('mycpBundle:ownership:list.html.twig', array(
                    'ownerships' => $ownerships,
                    'photo_count' => $data,
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
                    'filter_visit_date' => $filter_visit_date
        ));
    }

    public function delete_photoAction($id_ownership, $id_photo) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $dir = $this->container->getParameter('ownership.dir.photos');
        $dir_thumbnails = $this->container->getParameter('ownership.dir.thumbnails');
        $em = $this->getDoctrine()->getEntityManager();
        $data['languages'] = $em->getRepository('mycpBundle:lang')->get_all_languages();
        $photo = $em->getRepository('mycpBundle:photo')->find($id_photo);
        $photoLangs = $em->getRepository('mycpBundle:photoLang')->findBy(array('pho_lang_id_photo' => $id_photo));
        foreach ($photoLangs as $photoLang)
            $em->remove($photoLang);
        $photoDel = $photo;
        $album_photo = $em->getRepository('mycpBundle:ownershipPhoto')->findBy(array('own_pho_photo' => $id_photo));
        $em->remove($album_photo[0]);
        $em->remove($photo);
        $em->flush();
        @unlink($dir . $photoDel->getPhoName());
        @unlink($dir_thumbnails . $photoDel->getPhoName());
        $message = 'El fichero se ha eliminado satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);
        $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);

        $service_log = $this->get('log');
        $service_log->save_log('Delete photo, entity ' . $ownership->getOwnName(), 4);

        return $this->redirect($this->generateUrl('mycp_list_photos_ownership', array('id_ownership' => $id_ownership)));
    }

    public function delete_roomAction($id_room, $type) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();

        $em = $this->getDoctrine()->getManager();
        $ro = new room();

        $ro = $em->getRepository('mycpBundle:room')->find($id_room);
        $id_ownership = $ro->getRoomOwnership()->getOwnId();
        ;

        switch ($type) {
            case 0:
                $ro->setRoomActive(false);
                $em->persist($ro);
                $own = new ownership();
                $own = $ro->getRoomOwnership();
                $rooms = $em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $own->getOwnId()));
                $count = count($rooms);
                foreach ($rooms as $room) {
                    if (!$room->getRoomActive())
                        $count--;
                }

                if ($count <= 0) {
                    $status = new ownershipStatus();
                    $status = $em->getRepository('mycpBundle:ownershipstatus')->find(2);
                    $own->setOwnStatus($status);
                    $em->persist($own);
                }

                $em->flush();
                break;
            case 1:
                $own = new ownership();
                $own = $ro->getRoomOwnership();
                $em->remove($ro);
                $own->removeOwnRoom($ro);
                $em->flush();
                break;
            case 2:
                $ro->setRoomActive(true);
                $em->persist($ro);
                $em->flush();
                break;
        }

        return $this->redirect($this->generateUrl('mycp_edit_ownership', array('id_ownership' => $id_ownership)));
    }

    public function edit_ownershipAction($id_ownership, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $errors = array();
        $em = $this->getDoctrine()->getEntityManager();
        $ownership = new ownership();
        $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);
        $languages = $em->getRepository('mycpBundle:lang')->get_all_languages();
        $ownershipGeneralLangs = $em->getRepository('mycpBundle:ownershipGeneralLang')->findBy(array('ogl_ownership' => $id_ownership));
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
        $post['ownership_phone_code'] = $ownership->getOwnPhoneCode();
        $post['ownership_phone_number'] = $ownership->getOwnPhoneNumber();
        $post['ownership_email_1'] = $ownership->getOwnEmail1();
        $post['ownership_email_2'] = $ownership->getOwnEmail2();
        $post['ownership_category'] = $ownership->getOwnCategory();
        $post['ownership_type'] = $ownership->getOwnType();
        $post['comment'] = $ownership->getOwnComment();
        $post['ownership_percent_commission'] = $ownership->getOwnCommissionPercent();
        $post['recommendable'] = $ownership->getOwnRecommendable();
        $post['ownership_saler'] = $ownership->getOwnSaler();
        $post['ownership_visit_date'] = $ownership->getOwnVisitDate();
        $post['ownership_creation_date'] = $ownership->getOwnCreationDate();
        $post['ownership_last_update'] = $ownership->getOwnLastUpdate();

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
        $data['country_code'] = $ownership->getOwnAddressProvince()->getProvId();
        $data['municipality_code'] = $ownership->getOwnAddressMunicipality()->getMunId();
        $post['status'] = ($ownership->getOwnStatus() != null) ? $ownership->getOwnStatus()->getStatusId() : null;

        $post['top_20'] = ($post['top_20'] == false) ? 0 : 1;
        $post['recommendable'] = ($post['recommendable'] == false) ? 0 : 1;
        $post['facilities_breakfast'] = ($post['facilities_breakfast'] == false) ? 0 : 1;
        $post['facilities_dinner'] = ($post['facilities_dinner'] == false) ? 0 : 1;
        $post['facilities_parking'] = ($post['facilities_parking'] == false) ? 0 : 1;

        if ($ownership->getOwnWaterJacuzee() == 1) {
            $post['water_jacuzee'] = TRUE;
        }
        if ($ownership->getOwnWaterPiscina() == 1) {
            $post['water_piscina'] = TRUE;
        }
        if ($ownership->getOwnWaterSauna() == 1) {
            $post['water_sauna'] = TRUE;
        }

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
            $post['room_price_up_from_' . $a] = $rooms[$a - 1]->getRoomPriceUpFrom();
            $post['room_price_up_to_' . $a] = $rooms[$a - 1]->getRoomPriceUpTo();
            $post['room_price_down_from_' . $a] = $rooms[$a - 1]->getRoomPriceDownFrom();
            $post['room_price_down_to_' . $a] = $rooms[$a - 1]->getRoomPriceDownTo();
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
        return $this->render('mycpBundle:ownership:new.html.twig', array('languages' => $languages, 'count_rooms' => $count_rooms, 'post' => $post, 'data' => $data, 'errors' => $errors));
    }

    public function delete_ownershipAction($id_ownership) {

        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();

        $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);
        $old_code = $ownership->getOwnMcpCode();
        $ownershipGeneralLangs = $em->getRepository('mycpBundle:ownershipGeneralLang')->findBy(array('ogl_ownership' => $id_ownership));
        $ownershipDescLangs = $em->getRepository('mycpBundle:ownershipDescriptionLang')->findBy(array('odl_ownership' => $id_ownership));
        $ownershipKeywords = $em->getRepository('mycpBundle:ownershipKeywordLang')->findBy(array('okl_ownership' => $id_ownership));
        $ownershipRooms = $em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $id_ownership));
        $ownershipPhotos = $em->getRepository('mycpBundle:ownershipPhoto')->findBy(array('own_pho_own' => $id_ownership));
        $ownershipReservations = $em->getRepository('mycpBundle:ownershipReservation')->getOwnResByOwnership($id_ownership);
        $ownershipComments = $em->getRepository('mycpBundle:comment')->findBy(array('com_ownership' => $id_ownership));
        $userscasa = $em->getRepository('mycpBundle:userCasa')->findBy(array('user_casa_ownership' => $id_ownership));

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

        foreach ($userscasa as $usercasa) {
            $em->remove($usercasa);
        }

        $em->remove($ownership);
        $em->flush();

        $message = 'Propiedad eliminada satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        $service_log = $this->get('log');
        $service_log->save_log('Delete entity ' . $old_code, 4);

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

        if ($request->getMethod() == 'POST') {
            if ($request->request->get('new_room') == 1) {
                $count_rooms = $request->request->get('count_rooms') + 1;
                $data['new_room'] = TRUE;
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
                                $array_keys[$count] != 'ownership_homeowner_2'&& $array_keys[$count] != 'ownership_saler' &&
                                $array_keys[$count] != 'ownership_visit_date'
                        ) {
                            $errors[$array_keys[$count]] = $errors_validation = $this->get('validator')->validateValue($item, $not_blank_validator);
                            $data['count_errors']+=count($errors[$array_keys[$count]]);
                        }
                    }
                    if (strpos($array_keys[$count], 'room_') !== false && $array_keys[$count] != 'new_room') {
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
                        if ($user_db) {
                            $errors['user_name'] = 'Ya existe un usuario con ese nombre.';
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

                    /* if(strpos($array_keys[$count], 'description_')!==false)
                      {
                      $errors[$array_keys[$count]] = $errors_validation=$this->get('validator')->validateValue($item, $not_blank_validator);
                      $data['count_errors']+=count($errors[$array_keys[$count]]);
                      } */

                    /* if(strpos($array_keys[$count], 'keywords_')!==false)
                      {
                      $errors[$array_keys[$count]] = $errors_validation=$this->get('validator')->validateValue($item, $not_blank_validator);
                      $data['count_errors']+=count($errors[$array_keys[$count]]);
                      } */

                    /* if(strpos($array_keys[$count], 'ownership_language_')!==false)
                      {
                      $count_checkbox_lang++;
                      } */
                    $count++;
                }

                $errors['ownership_email_1_email'] = $this->get('validator')->validateValue($post['ownership_email_1'], $emailConstraint);
                $errors['ownership_email_2_email'] = $this->get('validator')->validateValue($post['ownership_email_2'], $emailConstraint);
                $data['count_errors']+=count($errors['ownership_email_1_email']);
                $data['count_errors']+=count($errors['ownership_email_2_email']);

                /* if($count_checkbox_lang==0)
                  {
                  $errors['checkbox_lang'] ='Debe seleccionar al menos un idioma.';
                  $data['count_errors']++;
                  } */

                $errors['facilities_breakfast'] = $this->get('validator')->validateValue($post['facilities_breakfast'], $not_blank_validator);
                $errors['status'] = $this->get('validator')->validateValue($post['status'], $not_blank_validator);
                $errors['facilities_dinner'] = $this->get('validator')->validateValue($post['facilities_dinner'], $not_blank_validator);
                $errors['facilities_parking'] = $this->get('validator')->validateValue($post['facilities_parking'], $not_blank_validator);
                $errors['geolocate_x'] = $this->get('validator')->validateValue($post['geolocate_x'], $not_blank_validator);
                $errors['geolocate_y'] = $this->get('validator')->validateValue($post['geolocate_y'], $not_blank_validator);

//var_dump($errors['geolocate']); exit();

                $data['count_errors']+=count($errors['facilities_breakfast']);
                $data['count_errors']+=count($errors['facilities_dinner']);
                $data['count_errors']+=count($errors['facilities_parking']);
                $data['count_errors']+=count($errors['geolocate_x']);
                $data['count_errors']+=count($errors['geolocate_y']);

                $count_rooms = $request->request->get('count_rooms');
                $data['country_code'] = $post['ownership_address_province'];
                $data['municipality_code'] = $post['ownership_address_municipality'];

                if ($data['count_errors'] == 0) {
// insert into database
                    if ($request->request->get('edit_ownership')) {
                        $id_own = $request->request->get('edit_ownership');
                        if ($request->request->get('status') == 4) {
                            return $this->redirect($this->generateUrl('mycp_delete_ownership', array('id_ownership' => $id_own)));
                        }
                        $service_log = $this->get('log');
                        $db_ownership = $em->getRepository('mycpBundle:ownership')->find($id_own);
                        $old_status = ($db_ownership->getOwnStatus() != null) ? $db_ownership->getOwnStatus()->getStatusId() : null; //$db_ownership->getOwnStatus()->getStatusId();
                        $new_status = $request->request->get('status');
                        $new_status_db = $em->getRepository('mycpBundle:ownershipStatus')->find($new_status)->getStatusName();
                        $any_edit = false;

                        if ($old_status != $new_status) {
                            $service_log->save_log('Edit entity (Change status. From ' . (($db_ownership->getOwnStatus() != null) ? $db_ownership->getOwnStatus()->getStatusId() : 'Sin Estado') . ' to ' . $new_status_db . ' ) ' . $post['ownership_mcp_code'], 4);
                            $any_edit = true;
                        }

                        $rooms_db = $em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $id_own));

                        $flag = 1;
                        $string_rooms_change_price = '';
                        if ($post['status'] == 1)
                            foreach ($rooms_db as $room) {
                                $db_price_up_from = $room->getRoomPriceUpFrom();
                                $post_price_up_from = $post['room_price_up_from_' . $flag];

                                $db_price_up_to = $room->getRoomPriceUpTo();
                                $post_price_up_to = $post['room_price_up_to_' . $flag];

                                $db_price_down_from = $room->getRoomPriceDownFrom();
                                $post_price_down_from = $post['room_price_down_from_' . $flag];

                                $db_price_down_to = $room->getRoomPriceDownTo();
                                $post_price_down_to = $post['room_price_down_to_' . $flag];

                                if ($db_price_up_from != $post_price_up_from) {
                                    $string_rooms_change_price.=' Room ' . $flag . ' changed price (High season "FROM") from ' . $db_price_up_from . ' to ' . $post_price_up_from . '.';
                                }

                                if ($db_price_up_to != $post_price_up_to) {
                                    $string_rooms_change_price.=' Room ' . $flag . ' changed price (High season "TO") from ' . $db_price_up_to . ' to ' . $post_price_up_to . '.';
                                }

                                if ($db_price_down_from != $post_price_down_from) {
                                    $string_rooms_change_price.=' Room ' . $flag . ' changed price (Low season "FROM") from ' . $db_price_down_from . ' to ' . $post_price_down_from . '.';
                                }

                                if ($db_price_down_to != $post_price_down_to) {
                                    $string_rooms_change_price.=' Room ' . $flag . ' changed price (Low season "TO") from ' . $db_price_down_to . ' to ' . $post_price_down_to . '.';
                                }

                                $flag++;
                            }

                        if ($string_rooms_change_price != '') {
                            $service_log->save_log('Edit entity. ' . $string_rooms_change_price . ' ' . $post['ownership_mcp_code'], 4);
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

                        if ($old_address_street != $new_address_street OR $old_number != $new_number OR $old_between_street_1 != $new_between_street_1 OR $old_between_street_2 != $new_between_street_2) {
                            $any_edit = true;
                            $service_log->save_log('Edit entity. Change address from ' . $old_address_street . ' street #' . $old_number . ' between ' . $old_between_street_1 . ' and ' . $old_between_street_2 .
                                    ' to ' . $new_address_street . ' street #' . $new_number . ' between ' . $new_between_street_1 . ' and ' . $new_between_street_2, 4);
                        }

                        $old_phone_number = $db_ownership->getOwnPhoneNumber();
                        $new_phone_number = $request->request->get('ownership_phone_number');

                        $old_phone_code = $db_ownership->getOwnPhoneCode();
                        $new_phone_code = $request->request->get('ownership_phone_code');

                        if ($old_phone_number != $new_phone_number OR $old_phone_code != $new_phone_code) {
                            $any_edit = true;
                            $service_log->save_log('Edit entity. Change phone number from ' . $old_phone_code . ' ' . $old_phone_number . ' to '
                                    . $new_phone_code . ' ' . $new_phone_number, 4);
                        }

                        if ($any_edit == false) {
                            $service_log->save_log('Edit entity ' . $post['ownership_mcp_code'], 4);
                        }

                        $em->getRepository('mycpBundle:ownership')->edit_ownership($post);

                        $message = 'Propiedad actualizada satisfactoriamente.';
                    } else {
                        $dir = $this->container->getParameter('user.dir.photos');
                        $factory = $this->get('security.encoder_factory');
                        if (isset($post['user_name']) && !empty($post['user_name']))
                            $em->getRepository('mycpBundle:ownership')->insert_ownership($post, $request, $dir, $factory, true);
                        else
                            $em->getRepository('mycpBundle:ownership')->insert_ownership($post, $request, $dir, $factory, false);
                        $message = 'Propiedad añadida satisfactoriamente.';
                        $service_log = $this->get('log');
                        $service_log->save_log('Create entity ' . $post['ownership_mcp_code'], 4);

                        //Enviar correo a los propietarios
                        if($post['status'] == ownershipStatus::STATUS_ACTIVE)
                        $this->send_owners_email($post['ownership_email_1'],
                                $post['ownership_email_2'],
                                $post['ownership_homeowner_1'],
                                $post['ownership_homeowner_2'],
                                $post['ownership_name'],
                                $post['ownership_mcp_code']);
                    }
                    $this->get('session')->getFlashBag()->add('message_ok', $message);
                    if ($request->get('save_reset_input') == 1) {
                        return $this->redirect($this->generateUrl('mycp_new_ownership'));
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

//$post['ownership_address_municipality'] = '';
        $languages = $em->getRepository('mycpBundle:lang')->get_all_languages();
        return $this->render('mycpBundle:ownership:new.html.twig', array('languages' => $languages, 'count_rooms' => $count_rooms, 'post' => $post, 'data' => $data, 'errors' => $errors, 'errors_tab' => $errors_tab));
    }

    public function edit_photoAction($id_photo, $id_ownership, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $post = '';
        $em = $this->getDoctrine()->getEntityManager();
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
                $message = 'Imágen actualizada satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);
                $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);

                $service_log = $this->get('log');
                $service_log->save_log('Edit photo, entity ' . $ownership->getOwnName(), 4);

                return $this->redirect($this->generateUrl('mycp_list_photos_ownership', array('id_ownership' => $id_ownership)));
            }
        }
        $photo = $em->getRepository('mycpBundle:photo')->find($id_photo);
        $data['languages'] = $em->getRepository('mycpBundle:lang')->get_all_languages();
        return $this->render('mycpBundle:ownership:photoEdit.html.twig', array(
                    'errors' => $errors,
                    'data' => $data,
                    'id_photo' => $id_photo,
                    'photo' => $photo,
                    'id_ownership' => $id_ownership,
                    'post' => $post));
    }

    public function send_ownershipAction($own_id) {
        $em = $this->getDoctrine()->getManager();
        $own = $em->getRepository('mycpBundle:ownership')->find($own_id);

        $own_mail_1 = $own->getOwnEmail1();
        $own_mail_2 = $own->getOwnEmail2();
        $owners_name_1 = $own->getOwnHomeowner1();
        $owners_name_2 = $own->getOwnHomeowner2();

        $this->send_owners_email($own_mail_1, $own_mail_2, $owners_name_1, $owners_name_2, $own->getOwnName(), $own->getOwnMcpCode());

        return $this->redirect($this->generateUrl('mycp_edit_ownership', array('id_ownership' => $own_id)));
    }

    private function send_owners_email($own_email_1, $own_email_2, $own_homeowner_1, $own_homeowner_2, $own_name, $own_mycp_code) {
        $service_email = $this->get('Email');
        try {
            $owners_name = $own_homeowner_1 . ( isset($own_homeowner_2) && isset($own_homeowner_1) && $own_homeowner_1 != "" && $own_homeowner_2 != "" ? " y " : "") . $own_homeowner_2;

            if (isset($own_email_1) && $own_email_1 != "") {
                $service_email->send_owners_mail($own_email_1, $owners_name, $own_name, $own_mycp_code);
            }
            if (isset($own_email_2) && $own_email_2 != "") {
                $service_email->send_owners_mail($own_email_2, $owners_name, $own_name, $own_mycp_code);
            }
            $message = 'El correo de instrucciones ha sido enviado satisfactoriamente al propietario';
            $this->get('session')->getFlashBag()->add('message_ok', $message);
        } catch (\Exception $e) {
            $message = 'Ha ocurrido un error en el envio del correo de instrucciones al propietario. ' . $e->getMessage();
            $this->get('session')->getFlashBag()->add('message_error_main', $message);
        }
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

}
