<?php

namespace Oforostianyi\Recipient;

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
     * @param string $recipient 380501234567
     * @return array|bool
     */
    public function checkRecipient(string $recipient = '')
    {
        return $this->checkPhoneInfo($recipient);
    }

    /**
     * проверяет информацию о телефоне и возвращает расширенную информацию по нему
     * @param string $phoneNumber 380501234567
     * @param string $mcc 0|255|260 etc
     * @return bool|array true|false|[]
     */
    function checkPhoneInfo(string $phoneNumber = '', string $mcc = '000'): bool|Recipient
    {

        $phoneNumber = $this->fixNumber($phoneNumber, $mcc);
        # если номер не подошел по параметрам - вернем ошибку
        if ($phoneNumber === false) return false;
        # если задана страна UA а первые цифры не 380 вернем ошибку
        if ($mcc == '255' && substr($phoneNumber, 0, 3) != 380) return false;

        $phoneNumberOK = false;
        # определим код страны (СС). 1-4 цифры
        for ($c = 1; $c <= 4; $c++) {
            $countryCode = substr($phoneNumber, 0, $c);
            # загрузим данные по коду страны.
            $currentPos = $this->mccMncService->getMccMncByCc($countryCode);
            if (!empty($currentPos)) {
                # определим оператора (NDC). 1-4 цифры
                for ($p = 1; $p <= 2; $p++) {
                    $providerCode = substr($phoneNumber, $c, $p);
                    if (!empty($currentPos[$providerCode])) {
                        $abonentNumber = substr($phoneNumber, $c + $p);
                        # Проверим есть ли субчасть (SUBC). берем от большего к меньшему
                        $currentSubPos =& $currentPos[$providerCode];
                        $finalMCCMNC = [];
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
     * проверяет номер на допустимую длину
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
     * очищает номер и фиксит его, если задан параметр страны и мы знаем как фиксить
     * @param string $phoneNumber 380501234567
     * @param int $mcc 0
     * @return bool|string
     */
    public function fixNumber(string $phoneNumber, int $mcc = 0): bool|string
    {
        // если номер пустой или егодлина менее 9 символов или если номер массив - вернем ошибку
        if (empty($phoneNumber) || strlen($phoneNumber) < 9) return false;
        // удалим из номера все, кроме цифр
        $phoneNumber = preg_replace("/([^0-9])+/", "", $phoneNumber);
        // удалим все нули с начала номера
        $phoneNumber = preg_replace("/^(0)+/", "", $phoneNumber);

        // допишем недостающий символы вначале номера до международного формата,
        // если задан $mcc применим правило корректировки к номеру

        switch ($mcc) {
            case "255" :
                $padString = '380000000000';
                $padLength = 12;
                break; # UA
            case "250" :
                $padString = '70000000000';
                $padLength = 11;
                break; # RU
            case "260" :
                $padString = '40000000000';
                $padLength = 11;
                break; # PL
            case "222" :
                $padString = '390000000000';
                $padLength = 12;
                break; # IT
            case "401" :
                $padString = '77000000000';
                $padLength = 11;
                break; # KZ
            case "257" :
                $padString = '375000000000';
                $padLength = 12;
                break; # BL
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