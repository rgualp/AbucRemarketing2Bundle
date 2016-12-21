<?php

/**
 * Description of AccommodationExcelReader
 *
 * @author Yanet Morales
 */

namespace MyCp\mycpBundle\Helpers;


class ReportParameterHelper  {

    const DATE_NOMENCLATOR_NAME = "date";
    const DATE_RANGE_NOMENCLATOR_NAME = "dateRange";
    const LOCATION_NOMENCLATOR_NAME = "location";
    const LOCATION_FULL_NOMENCLATOR_NAME = "location_full";
    const ACCOMMODATION_MODALITY_NOMENCLATOR_NAME = "accommodationModality";
}

class AccommodationModality{
    const RR = "rrModality";
    const RI = "riModality";
    const NORMAL = "normalModality";
}

?>
