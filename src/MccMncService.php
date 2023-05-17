<?php

namespace Oforostianyi\Recipient;

/**
 * Olexander Forostianyi aka ZViruS
 * 2023-05-17
 */
class MccMncService
{
    private MccMncRepository $mccMncRepository;

    public function __construct(MccMncRepository $mccMncRepository)
    {
        $this->mccMncRepository = $mccMncRepository;
    }

    public function getMccMncByCc(int $cc)
    {
        return $this->mccMncRepository->getMccMncByCc($cc);
    }
}