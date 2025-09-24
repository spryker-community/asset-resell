<?php

declare(strict_types=1);

namespace SprykerCommunity\Zed\AssetResell\Communication\Plugin\Event\Listener;

use Spryker\Zed\Event\Dependency\Plugin\EventBulkHandlerInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \Pyz\Zed\ResaleOffer\Business\ResaleOfferFacade getFacade()
 */
class SspAssetResaleOfferEventListener extends AbstractPlugin implements EventBulkHandlerInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventEntityTransfers
     * @param string $eventName
     *
     * @return void
     */
    public function handleBulk(array $eventEntityTransfers, $eventName)
    {
        foreach ($eventEntityTransfers as $eventEntityTransfer) {
            $this->getFacade()->createResaleOfferByAssetId($eventEntityTransfer->getId());
        };
    }
}
