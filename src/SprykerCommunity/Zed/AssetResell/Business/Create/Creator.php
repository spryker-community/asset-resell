<?php

declare(strict_types=1);

namespace SprykerCommunity\Zed\AssetResell\Business\Create;

use ArrayObject;
use Generated\Shared\Transfer\MerchantCriteriaTransfer;
use Generated\Shared\Transfer\MerchantTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Generated\Shared\Transfer\ProductOfferCriteriaTransfer;
use Generated\Shared\Transfer\ProductOfferTransfer;
use Generated\Shared\Transfer\ResaleOfferResponseTransfer;
use Generated\Shared\Transfer\SspAssetConditionsTransfer;
use Generated\Shared\Transfer\SspAssetCriteriaTransfer;
use Generated\Shared\Transfer\SspAssetTransfer;
use Pyz\Shared\SelfServicePortal\SelfServicePortalConfig;
use Spryker\Zed\Merchant\Business\MerchantFacade;
use Spryker\Zed\Product\Business\ProductFacade;
use Spryker\Zed\ProductOffer\Business\ProductOfferFacade;
use SprykerFeature\Zed\SelfServicePortal\Business\SelfServicePortalFacade;

class Creator
{
    public function createResaleOffer(int $idAsset): ResaleOfferResponseTransfer
    {
        $assetTransfer = $this->findAssetToCreateOffer($idAsset);

        //TODO: remove
        $assetTransfer->setSku('419869');

        if ($assetTransfer === null || $assetTransfer->getSku() === null || $assetTransfer->getStatus() !== SelfServicePortalConfig::STATUS_APPROVED) {
            return (new ResaleOfferResponseTransfer())->setIsSuccessful(false);
        }

        $merchantTransfer = $this->getMerchant();

        $productOfferCollectionTransfer = (new ProductOfferFacade())->get(
            (new ProductOfferCriteriaTransfer())->setConcreteSku($assetTransfer->getSku())
                ->setMerchantReferences([$merchantTransfer->getMerchantReference()])
        );

        foreach ($productOfferCollectionTransfer->getProductOffers() as $productOfferTransfer) {
            if ($productOfferTransfer->getFkSspAsset() === $assetTransfer->getIdSspAsset()) {
                return (new ResaleOfferResponseTransfer())->setIsSuccessful(false);
            }
        }

        $productOfferTransfer = $this->createProductOfferFromAssetAndMerchant($assetTransfer, $merchantTransfer);

        return (new ResaleOfferResponseTransfer())->setIsSuccessful($productOfferTransfer->getIdProductOffer() !== null);
    }

    private function getMerchant(): MerchantTransfer
    {
        return (new MerchantFacade())->findOne((new MerchantCriteriaTransfer())->setIdMerchant(1));
    }

    private function findAssetToCreateOffer(int $idAsset): ?SspAssetTransfer
    {
        $assetCollectionTransfer = (new SelfServicePortalFacade())->getSspAssetCollection(
            (new SspAssetCriteriaTransfer())->setSspAssetConditions(
                (new SspAssetConditionsTransfer())->setSspAssetIds([$idAsset])
            )
        );

        foreach ($assetCollectionTransfer->getSspAssets() as $assetTransfer) {
            return $assetTransfer;
        }

        return null;
    }
    private function createProductOfferFromAssetAndMerchant(
        SspAssetTransfer $sspAssetTransfer,
        MerchantTransfer $merchantTransfer
    ): ProductOfferTransfer {
        $idProductConcrete = (new ProductFacade())->findProductConcreteIdBySku($sspAssetTransfer->getSku());


        $productOfferTransfer = new ProductOfferTransfer();
        $productOfferTransfer
            ->setConcreteSku($sspAssetTransfer->getSku())
            ->setMerchantReference($merchantTransfer->getMerchantReference())
            ->setIdProductConcrete($idProductConcrete)
            ->setfkSspAsset($sspAssetTransfer->getIdSspAsset())
            ->setIsActive(true)
            ->setPrices(new ArrayObject(
                [
                    (new PriceProductTransfer())->set
                ]
            ));

        return (new ProductOfferFacade())->create($productOfferTransfer);
    }
}
