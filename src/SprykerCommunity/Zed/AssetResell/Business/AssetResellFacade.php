<?php

declare(strict_types=1);

namespace SprykerCommunity\Zed\AssetResell\Business;

use Generated\Shared\Transfer\ResaleOfferResponseTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;
use SprykerCommunity\Zed\AssetResell\Business\Create\Creator;

class AssetResellFacade extends AbstractFacade
{
    public function createResaleOfferByAssetId(int $idAsset): ResaleOfferResponseTransfer
    {
        return (new Creator())->createResaleOffer($idAsset);
    }
}
