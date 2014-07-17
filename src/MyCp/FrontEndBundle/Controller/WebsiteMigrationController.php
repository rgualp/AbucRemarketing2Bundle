<?php

namespace MyCp\FrontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class WebsiteMigrationController extends Controller {

    public function permanentRedirectAction($redirectTo, $locale = 'es', $args = array()) {
        return $this->executeRedirect(301, $redirectTo, $locale, $args);
    }

    public function temporaryRedirectAction($redirectTo, $locale = 'es', $args = array()) {
        return $this->executeRedirect(302, $redirectTo, $locale, $args);
    }

    private function executeRedirect($status, $redirectTo, $locale, $args) {
        // The old website uses non-standard 'ge' instead of 'de' for the German locale
        $locale = $locale === 'ge' ? 'de' : $locale;
        $locale = array('locale' => $locale, '_locale' => $locale);
        return $this->redirect($this->generateUrl($redirectTo, array_merge($locale, $args)), $status);
    }
}
