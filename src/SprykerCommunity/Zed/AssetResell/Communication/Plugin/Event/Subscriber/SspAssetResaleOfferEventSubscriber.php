<?php

declare(strict_types=1);

namespace SprykerCommunity\Zed\AssetResell\Communication\Plugin\Event\Subscriber;

use SprykerCommunity\Zed\AssetResell\Communication\Plugin\Event\Listener\SspAssetResaleOfferEventListener;
use Spryker\Zed\Event\Dependency\EventCollectionInterface;
use Spryker\Zed\Event\Dependency\Plugin\EventSubscriberInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use SprykerFeature\Shared\SelfServicePortal\SelfServicePortalConfig;

class SspAssetResaleOfferEventSubscriber extends AbstractPlugin implements EventSubscriberInterface
{
    public function getSubscribedEvents(EventCollectionInterface $eventCollection): EventCollectionInterface
    {
        $this->addUpdateSspAssetListener($eventCollection);

        return $eventCollection;
    }

    private function addUpdateSspAssetListener(EventCollectionInterface $eventCollection)
    {
        $eventCollection->addListener(SelfServicePortalConfig::ENTITY_SPY_SSP_ASSET_UPDATE, new SspAssetResaleOfferEventListener(), 0, null, null);

        return $this;
    }
}
