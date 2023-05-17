<?php

namespace Oforostianyi\Recipient;

/**
 * Olexander Forostianyi aka ZViruS
 * 2023-05-17
 */

class RecipientService
{
    private Recipient $recipient;
    private mccMncService $mccMncService;

    public function __construct(DatabaseConnection $databaseConnection)
    {
        $this->recipient = new Recipient();
        $this->mccMncService = new MccMncService(new MccMncRepository($databaseConnection));
    }

    /**
     * checks information about the phone and returns extended information on it
     * @param string $phoneNumber 380501234567
     * @param string $mcc 00|255|260 etc
     * @return bool|Recipient
     */
    function check(string $phoneNumber = '', string $mcc = '000')
    {

        $phoneNumber = $this->fixNumber($phoneNumber, $mcc);
        // if the number does not match the parameters - return an error
        if ($phoneNumber === false) return false;
        // Hardcode. if the country is UA and the first digits are not 380, we will return an error
        if ($mcc == '255' && substr($phoneNumber, 0, 3) != 380) return false;

        $phoneNumberOK = false;
        $finalMCCMNC = [];
        // define the country code (CC). 1-4 digits
        for ($c = 1; $c <= 4; $c++) {
            $countryCode = substr($phoneNumber, 0, $c);
            // load data by country code.
            $currentPos = $this->mccMncService->getMccMncByCc($countryCode);
            if (!empty($currentPos)) {
                // define an operator (NDC). 1-4 digits
                for ($p = 1; $p <= 2; $p++) {
                    $providerCode = substr($phoneNumber, $c, $p);
                    if (!empty($currentPos[$providerCode])) {
                        $abonentNumber = substr($phoneNumber, $c + $p);
                        // Check if there is a subpart (SUBC). Taking from largest to smallest
                        $currentSubPos =& $currentPos[$providerCode];
                        for ($i = 6; $i >= 1; $i--) {
                            $firstDigits = substr($abonentNumber, 0, $i);
                            if (isset($currentSubPos[$firstDigits])) {
                                if ($this->checkLength($providerCode . $abonentNumber, $currentSubPos[$firstDigits]['length'])) {
                                    $finalMCCMNC = $currentSubPos[$firstDigits];
                                    break;
                                }
                            }
                        }
                        if (empty($finalMCCMNC)) {
                            if (!empty($currentSubPos['-'])) {
                                if ($this->checkLength($providerCode . $abonentNumber, $currentSubPos['-']['length'])) {
                                    $finalMCCMNC = $currentSubPos['-'];
                                }
                            }
                        }
                        if (!empty($finalMCCMNC)) {
                            $finalMCCMNC['msisdn'] = $countryCode . $providerCode . $abonentNumber;
                            $phoneNumberOK = true;
                            break 2;
                        }
                    }
                }
            }
        }
        if ($phoneNumberOK) {
            foreach ($finalMCCMNC as $key => $value) {
                $this->recipient->$key = $value;
            }
            return $this->recipient;
        }
        return false;
    }

    /**
     * checks the number for valid length
     * @param string $localNumber номер без кода страны
     * @param string $requireLength единичная длина или несколько допустимых параметров, через запятую.
     * @return bool
     */
    function checkLength(string $localNumber, string $requireLength = '10'): bool
    {
        $lengths = explode(',', $requireLength);
        foreach ($lengths as $length) {
            if ($length == strlen($localNumber)) return true;
        }
        return false;
    }

    /**
     * clears the number and fixes it if the country parameter is given and we know how to fix it
     * @param string $phoneNumber 380501234567
     * @param string $mcc 0
     * @return bool|string
     */
    public function fixNumber(string $phoneNumber, string $mcc = '000')
    {
        // if the number is empty or its length is less than 9 characters or if the number is an array - return an error
        if (empty($phoneNumber) || strlen($phoneNumber) < 9) return false;
        // remove everything except digits from the number
        $phoneNumber = preg_replace("/([^0-9])+/", "", $phoneNumber);
        // remove all zeros from the beginning of the number
        $phoneNumber = preg_replace("/^(0)+/", "", $phoneNumber);
        // Hardcoded part. Bad practice.
        // Allows you to correct the local number to the international format if the Mobile Country Code is specified.
        // Add the missing characters at the beginning of the number to the international format,
        // if $mcc is given, apply the adjustment rule to the number
        // Ex. If $mcc == 255 convert 0631234567 => 380631234567

        switch ($mcc) {
            case "255" : // UA
                $padString = '380000000000';
                $padLength = 12;
                break;
            case "250" : // RU
                $padString = '70000000000';
                $padLength = 11;
                break;
            case "260" : // PL
                $padString = '40000000000';
                $padLength = 11;
                break;
            case "222" : // IT
                $padString = '390000000000';
                $padLength = 12;
                break;
            case "401" : // KZ
                $padString = '77000000000';
                $padLength = 11;
                break;
            case "257" : // BL
                $padString = '375000000000';
                $padLength = 12;
                break;
            default:
                $padString = '0';
                $padLength = 14;
                break;
        }

        $phoneNumber = str_pad($phoneNumber, $padLength, $padString, STR_PAD_LEFT);
        // удалим все нули с начала номера
        $phoneNumber = preg_replace("/^(0)+/", "", $phoneNumber);
        // максимальная длина номера не может превышать 13 символов
        return (strlen($phoneNumber) > $padLength || strlen($phoneNumber) < 10) ? false : $phoneNumber;
    }

}