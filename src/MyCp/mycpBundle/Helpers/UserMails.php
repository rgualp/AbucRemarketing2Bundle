<?php

/**
 * Description of OwnershipStatuses
 *
 * @author Yanet
 */

namespace MyCp\mycpBundle\Helpers;

class UserMails
{

    public static function sendOwnersMail($controller, $own_email_1, $own_email_2, $own_homeowner_1, $own_homeowner_2, $own_name, $own_mycp_code)
    {
        if((isset($own_email_1) && $own_email_1 != "") || (isset($own_email_2) && $own_email_2 != "")) {
            $service_email = $controller->get('Email');
            try {
                $owners_name = $own_homeowner_1 . (isset($own_homeowner_2) && isset($own_homeowner_1) && $own_homeowner_1 != "" && $own_homeowner_2 != "" ? " y " : "") . $own_homeowner_2;

                if(isset($own_email_1) && $own_email_1 != "") {
                    $service_email->sendOwnersMail($own_email_1, $owners_name, $own_name, $own_mycp_code);
                }
                if(isset($own_email_2) && $own_email_2 != "") {
                    $service_email->sendOwnersMail($own_email_2, $owners_name, $own_name, $own_mycp_code);
                }
                $message = 'El correo de instrucciones ha sido enviado satisfactoriamente al propietario';
                $controller->get('session')->getFlashBag()->add('message_ok', $message);
            } catch (\Exception $e) {
                $message = 'Ha ocurrido un error en el envio del correo de instrucciones al propietario. ' . $e->getMessage();
                $controller->get('session')->getFlashBag()->add('message_error_main', $message);
            }
        }
    }

    public static function sendCreateUserCasaMail($controller, $user_email, $user_name, $user_full_name, $secret_token, $own_name, $own_mycp_code)
    {
        if((isset($user_email) && $user_email != "")) {
            $service_email = $controller->get('Email');
            try {
                if(isset($user_email) && $user_email != "") {
                    $service_email->sendCreateUserCasaMail($user_email, $user_name, $user_full_name, $secret_token, $own_mycp_code, $own_name);
                }
                $message = 'El correo notificando la creación de la cuenta de usuario ha sido enviado satisfactoriamente al usuario ' . $user_full_name;
                $controller->get('session')->getFlashBag()->add('message_ok', $message);
            } catch (\Exception $e) {
                $message = 'Ha ocurrido un error en el envio del correo de notificación de creación de la cuenta de usuario al propietario. ' . $e->getMessage();
                $controller->get('session')->getFlashBag()->add('message_error_main', $message);
            }
        }
        else {
            $message = 'No se ha enviado la notificación porque el correo electrónico está en blanco.';
            $controller->get('session')->getFlashBag()->add('message_error_main', $message);
        }
    }

    public static function sendCreateUserCasaMailBackend($controller, $user, $password, $accommodation, $container)
    {
        $user_email = $user->getUserEmail();
        if((isset($user_email) && $user_email != "")) {
            $service_email = $controller->get('Email');
            try {
                if(isset($user_email) && $user_email != "") {
                    $service_email->sendCreateUserCasaMailBackend($user_email, $user, $password, $accommodation, $container);
                }
                $message = 'El correo notificando la creación de la cuenta de usuario ha sido enviado satisfactoriamente al usuario ' . $user->getUserEmail();
                $controller->get('session')->getFlashBag()->add('message_ok', $message);
            } catch (\Exception $e) {
                $message = 'Ha ocurrido un error en el envio del correo de notificación de creación de la cuenta de usuario al propietario. ' . $e->getMessage();
                $controller->get('session')->getFlashBag()->add('message_error_main', $message);
            }
        }
        else {
            $message = 'No se ha enviado la notificación porque el correo electrónico está en blanco.';
            $controller->get('session')->getFlashBag()->add('message_error_main', $message);
        }
    }

    public static function sendCreateUserCasaMailNew($controller, $user_email, $user_name, $user_full_name, $secret_token, $own_name, $own_mycp_code)
    {
        if((isset($user_email) && $user_email != "")) {
            $service_email = $controller->get('Email');
            try {
                if(isset($user_email) && $user_email != "") {
                    $service_email->sendCreateUserCasaMailNew($user_email, $user_name, $user_full_name, $secret_token, $own_mycp_code, $own_name);
                }
                $message = 'El correo notificando la creación de la cuenta de usuario ha sido enviado satisfactoriamente al usuario ' . $user_full_name;
                $controller->get('session')->getFlashBag()->add('message_ok', $message);
            } catch (\Exception $e) {
                $message = 'Ha ocurrido un error en el envio del correo de notificación de creación de la cuenta de usuario al propietario. ' . $e->getMessage();
                $controller->get('session')->getFlashBag()->add('message_error_main', $message);
            }
        }
        else {
            $message = 'No se ha enviado la notificación porque el correo electrónico está en blanco.';
            $controller->get('session')->getFlashBag()->add('message_error_main', $message);
        }
    }

    /**
     * @param $controller
     * @param $user_email
     * @param $user_name
     * @param $user_full_name
     * @param $secret_token
     * @param $user_locale
     * @param $agency
     * @param bool $beta
     * @internal param $own_name
     * @internal param $own_mycp_code
     */
    public static function sendCreateUserPartner($controller, $user_email, $user_name, $user_full_name, $secret_token, $user_locale, $agency, $beta = null)
    {
        if((isset($user_email) && $user_email != "")) {
            $service_email = $controller->get('Email');
            try {
                if(isset($user_email) && $user_email != "") {
                    $service_email->sendCreateUserPartner($user_email, $user_name, $user_full_name, $secret_token, $user_locale, $agency, $beta);
                }
                $message = 'El correo notificando la creación de la cuenta de usuario ha sido enviado satisfactoriamente al usuario ' . $user_full_name;
                $controller->get('session')->getFlashBag()->add('message_ok', $message);
            } catch (\Exception $e) {
                $message = 'Ha ocurrido un error en el envio del correo de notificación de creación de la cuenta de usuario al propietario. ' . $e->getMessage();
                $controller->get('session')->getFlashBag()->add('message_error_main', $message);
            }
        }
        else {
            $message = 'No se ha enviado la notificación porque el correo electrónico está en blanco.';
            $controller->get('session')->getFlashBag()->add('message_error_main', $message);
        }
    }

}

?>
