<?php

namespace Oforostianyi\Recipient;
class MccMncService
{
    private MccMncRepository $mccMncRepository;

    public function __construct(MccMncRepository $mccMncRepository)
    {
        $this->mccMncRepository = $mccMncRepository;
    }

    public function getMccMncByCc(int $cc): ?array
    {
        return $this->mccMncRepository->getMccMncByCc($cc);
    }
}