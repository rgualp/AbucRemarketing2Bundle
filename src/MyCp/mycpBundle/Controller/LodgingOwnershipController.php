<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\ownershipStatus;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class LodgingOwnershipController extends Controller
{
    public function edit_ownershipAction(Request $request)
    {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $user = $this->get('security.context')->getToken()->getUser();
        $ownership = null;
        $errors = array();
        $data['count_errors'] = 0;
        $post = array();
        $count_rooms = 0;

        if ($user->getUserRole() == 'ROLE_CLIENT_CASA') {
            $em = $this->getDoctrine()->getManager();
            $user_casa = $em->getRepository('mycpBundle:userCasa')->get_user_casa_by_user_id($user->getUserId());
            $ownership = $user_casa->getUserCasaOwnership();
            $rooms = $em->getRepository('mycpBundle:room')->findby(array('room_ownership' => $ownership->getOwnId(), "room_active" => true));
            $count_rooms = count($rooms);
            $post['ownership_id'] = $ownership->getOwnId();
            $data['id_ownership'] = $ownership->getOwnId();
            $post['ownership_name'] = $ownership->getOwnName();
            $data['name_ownership'] = $ownership->getOwnName();
            $post['ownership_address_street'] = $ownership->getOwnAddressStreet();
            $post['ownership_address_number'] = $ownership->getOwnAddressNumber();
            $post['ownership_address_between_street_1'] = $ownership->getOwnAddressBetweenStreet1();
            $post['ownership_address_between_street_2'] = $ownership->getOwnAddressBetweenStreet2();
            $post['ownership_address_province'] = $ownership->getOwnAddressProvince()->getProvId();
            $post['ownership_address_municipality'] = $ownership->getOwnAddressMunicipality()->getMunId();
            $post['ownership_mobile_number'] = $ownership->getOwnMobileNumber();
            $post['ownership_phone_code'] = $ownership->getOwnPhoneCode();
            $post['ownership_phone_number'] = $ownership->getOwnPhoneNumber();
            $post['ownership_email_1'] = $ownership->getOwnEmail1();
            $post['ownership_email_2'] = $ownership->getOwnEmail2();
            $post['ownership_creation_date'] = $ownership->getOwnCreationDate();
            $post['ownership_last_update'] = $ownership->getOwnLastUpdate();

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
        } else {
            $message = "Usted no tiene asociada propiedad a su cuenta de usuario";
            $this->get('session')->getFlashBag()->add('message_error_main', $message);
            return $this->redirect($this->generateUrl('mycp_lodging_front'));

        }

        return $this->render('mycpBundle:ownership:short_edit_form.html.twig', array('data' => $data, 'errors' => $errors, 'count_rooms' => $count_rooms, 'post' => $post));
    }

    public function update_ownershipAction(Request $request)
    {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $count_rooms = 1;
        $post = $request->request->getIterator()->getArrayCopy();
        $errors = array();
        $data = array();
        $data['country_code'] = '';
        $data['municipality_code'] = '';
        $data['count_errors'] = 0;
        $own = null;

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
                        if ($array_keys[$count] != 'ownership_email_1' &&
                            $array_keys[$count] != 'ownership_mobile_number' &&
                            $array_keys[$count] != 'ownership_phone_number' &&
                            $array_keys[$count] != 'ownership_email_2'

                        ) {
                            $errors[$array_keys[$count]] = $errors_validation = $this->get('validator')->validateValue($item, $not_blank_validator);
                            $data['count_errors'] += count($errors[$array_keys[$count]]);
                        }
                    }


                    $count++;
                }

                $errors['ownership_email_1_email'] = $this->get('validator')->validateValue($post['ownership_email_1'], $emailConstraint);
                $errors['ownership_email_2_email'] = $this->get('validator')->validateValue($post['ownership_email_2'], $emailConstraint);
                $data['count_errors'] += count($errors['ownership_email_1_email']);
                $data['count_errors'] += count($errors['ownership_email_2_email']);

                if ($data['count_errors'] == 0) {
// insert into database
                    if ($request->request->get('edit_ownership')) {
                        $id_own = $request->request->get('edit_ownership');
                        $service_log = $this->get('log');
                        $db_ownership = $em->getRepository('mycpBundle:ownership')->find($id_own);
                        $any_edit = false;

                        $old_phone_number = $db_ownership->getOwnPhoneNumber();
                        $new_phone_number = $request->request->get('ownership_phone_number');

                        $old_phone_code = $db_ownership->getOwnPhoneCode();
                        $new_phone_code = $request->request->get('ownership_phone_code');

                        if ($old_phone_number != $new_phone_number OR $old_phone_code != $new_phone_code) {
                            $any_edit = true;
                            $service_log->saveLog('Edit entity. Change phone number from ' . $old_phone_code . ' ' . $old_phone_number . ' to '
                            . $new_phone_code . ' ' . $new_phone_number, BackendModuleName::MODULE_LODGING_OWNERSHIP);
                        }

                        if ($any_edit == false) {
                            $service_log->saveLog('Edit entity ' . $db_ownership->getOwnMcpCode(), BackendModuleName::MODULE_LODGING_OWNERSHIP);
                        }

                        $em->getRepository('mycpBundle:ownership')->short_edit_ownership($post);

                        $message = 'Propiedad actualizada satisfactoriamente.';
                    } else {

                    }
                    $this->get('session')->getFlashBag()->add('message_ok', $message);
                    return $this->redirect($this->generateUrl('mycp_lodging_front'));

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


        }

        return $this->render('mycpBundle:ownership:short_edit_form.html.twig', array('count_rooms' => $count_rooms, 'post' => $post, 'data' => $data, 'errors' => $errors, 'errors_tab' => $errors_tab));

    }

    public function send_mailAction(Request $request)
    {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $to_email = 'casa@mycasaparticular.com';
        $user = $this->get('security.context')->getToken()->getUser();
        $ownership = null;
        $user_casa = null;
        $message_for_change = $request->get('message_for_change');
        $owner_name = '';
        $owner_phone = '';
        $owner_mobile = '';
        $owner_email = '';
        $ownership_name = '';
        $ownership_mcp = '';
        if ($user->getUserRole() == 'ROLE_CLIENT_CASA') {
            $em = $this->getDoctrine()->getManager();
            $user_casa = $em->getRepository('mycpBundle:userCasa')->get_user_casa_by_user_id($user->getUserId());
            $ownership = $user_casa->getUserCasaOwnership();
            $owner_name = $user_casa->getUserCasaUser()->getUserName() . " " . $user_casa->getUserCasaUser()->getUserLastName();
            $owner_phone = $ownership->getOwnPhoneCode() . " " . $ownership->getOwnPhoneNumber();
            $owner_mobile = $ownership->getOwnMobileNumber();
            $owner_email = $ownership->getOwnEmail1();
            $ownership_name = $ownership->getOwnName();
            $ownership_mcp = $ownership->getOwnMcpCode();

            // Enviando mail al equipo de MyCasaParticular
            $service_email = $this->get('Email');

            $body = $this->render('mycpBundle:mail:newChangeOwnershipMailBody.html.twig', array(
                'owner_name' => $owner_name,
                'owner_phone' => $owner_phone,
                'owner_mobile' => $owner_mobile,
                'owner_email' => $owner_email,
                'ownership_name' => $ownership_name,
                'ownership_mcp' => $ownership_mcp,
                'message_for_change' => $message_for_change
            ));

            $subject = 'Solicitud de cambio en la información de una propiedad por su propietario';
            $service_email->sendEmail($subject, 'noreplay@mycasaparticular.com', $subject , $to_email, $body);
            $message_to_show = "Se ha enviado un mensaje de correo electrónico al equipo de MyCasaParticular con su solicitud de cambio con éxito";
            $this->get('session')->getFlashBag()->add('message_ok', $message_to_show);
        }
        return $this->redirect($this->generateUrl('mycp_short_edit_ownership'));

    }
}
