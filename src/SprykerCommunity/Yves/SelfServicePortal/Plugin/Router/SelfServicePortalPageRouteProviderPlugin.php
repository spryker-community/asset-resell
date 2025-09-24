<?php

namespace SprykerCommunity\Yves\SelfServicePortal\Plugin\Router;

use Spryker\Yves\Router\Route\RouteCollection;
use SprykerFeature\Yves\SelfServicePortal\Plugin\Router\SelfServicePortalPageRouteProviderPlugin as SprykerSelfServicePortalPageRouteProviderPlugin;

class SelfServicePortalPageRouteProviderPlugin extends SprykerSelfServicePortalPageRouteProviderPlugin
{
    /**
     * @var string
     */
    public const ROUTE_NAME_ASSET_RESELL = 'customer/ssp-asset/resell';

    /**
     * @var string
     */
    protected const PATTERN_ASSET_RESELL = '/customer/ssp-asset/resell';

    /**
     * @var string
     */
    protected const REFERENCE_REGEX = '[a-zA-Z0-9-_]+';

    /**
     * {@inheritDoc}
     * - Adds routes to the route collection.
     *
     * @api
     *
     * @param \Spryker\Yves\Router\Route\RouteCollection $routeCollection
     *
     * @return \Spryker\Yves\Router\Route\RouteCollection
     */
    public function addRoutes(RouteCollection $routeCollection): RouteCollection
    {
        $routeCollection = parent::addRoutes($routeCollection);
        $routeCollection = $this->addAssetResellRoute($routeCollection);

        return $routeCollection;
    }

    /**
     * @param \Spryker\Yves\Router\Route\RouteCollection $routeCollection
     *
     * @return \Spryker\Yves\Router\Route\RouteCollection
     */
    protected function addAssetResellRoute(RouteCollection $routeCollection): RouteCollection
    {
        $route = $this->buildRoute(static::PATTERN_ASSET_RESELL, 'SelfServicePortal', 'Asset', 'resellAction');
        $route = $route->setRequirement('reference', static::REFERENCE_REGEX);
        $routeCollection->add(static::ROUTE_NAME_ASSET_RESELL, $route);

        return $routeCollection;
    }
}
