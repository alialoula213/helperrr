<?php
/*
 * @ Decoder By DoniaWeB.com
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

namespace App\Traits;

interface LicenseTrait
{
    private function registerLicenseApiRoute();
    private function checkLicense($reason, $params);
    private function createLicenseFile($next_date);
    private function validateLicenseFile();
    private function parseDomain();
    private function disabledFile($next_check, $message);
    private function showLicenseView($message);
    private function errorResponse($message, $params);
}

?>