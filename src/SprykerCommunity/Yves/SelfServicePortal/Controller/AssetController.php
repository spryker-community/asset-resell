<?php

namespace SprykerCommunity\Yves\SelfServicePortal\Controller;

use Generated\Shared\Transfer\LocalizedAttributesTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Generated\Shared\Transfer\SspAssetBusinessUnitAssignmentTransfer;
use Generated\Shared\Transfer\SspAssetCollectionRequestTransfer;
use Generated\Shared\Transfer\SspAssetConditionsTransfer;
use Generated\Shared\Transfer\SspAssetCriteriaTransfer;
use Generated\Shared\Transfer\SspAssetTransfer;
use SprykerFeature\Shared\SelfServicePortal\Plugin\Permission\CreateSspAssetPermissionPlugin;
use SprykerFeature\Shared\SelfServicePortal\Plugin\Permission\UpdateSspAssetPermissionPlugin;
use SprykerFeature\Yves\SelfServicePortal\Controller\AssetController as SprykerAssetController;
use SprykerFeature\Yves\SelfServicePortal\Plugin\Router\SelfServicePortalPageRouteProviderPlugin;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Spryker\Yves\Kernel\View\View;

/**
 * @method \Pyz\Yves\SelfServicePortal\SelfServicePortalFactory getFactory()
 */
class AssetController extends SprykerAssetController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     *
     * @return \Spryker\Yves\Kernel\View\View|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request): View|RedirectResponse
    {
        $companyUserTransfer = $this->getFactory()
            ->getCompanyUserClient()
            ->findCompanyUser();

        if (!$companyUserTransfer) {
            $this->addErrorMessage('company.error.company_user_not_found');

            return $this->redirectResponseInternal(static::ROUTE_CUSTOMER_OVERVIEW);
        }

        if (!$this->can(CreateSspAssetPermissionPlugin::KEY)) {
            throw new AccessDeniedHttpException('self_service_portal.asset.access.denied');
        }

        // Get SKU from request if provided
        $sku = $request->query->get('sku');
        $sspAssetTransfer = new SspAssetTransfer();

        if ($sku) {
            // Fetch product data by SKU
            $productConcreteTransfer = $this->getProductBySku($sku);

            if ($productConcreteTransfer) {
                // Prefill the transfer with product data
                $sspAssetTransfer->setName($productConcreteTransfer['name']);
                $sspAssetTransfer->setSerialNumber($sku);
                $sspAssetTransfer->setNote(sprintf('Created based on product %s', $sku));
                $sspAssetTransfer->setSku($sku);
            }
        }

        $sspAssetCreateForm = $this->getFactory()
            ->createAssetForm($sspAssetTransfer)
            ->handleRequest($request);

        if ($sspAssetCreateForm->isSubmitted() && $sspAssetCreateForm->isValid()) {
            $sspAssetTransfer = $this->getFactory()->createSspAssetFormDataToTransferMapper()->mapFormDataToSspAssetTransfer(
                $sspAssetCreateForm,
                $sspAssetCreateForm->getData(),
            );

            $sspAssetTransfer->addBusinessUnitAssignment(
                (new SspAssetBusinessUnitAssignmentTransfer())->setCompanyBusinessUnit(
                    $companyUserTransfer->getCompanyBusinessUnit(),
                ),
            );

            $sspAssetTransfer->setCompanyBusinessUnit($companyUserTransfer->getCompanyBusinessUnit());

            $sspAssetCollectionResponseTransfer = $this->getClient()->createSspAssetCollection(
                (new SspAssetCollectionRequestTransfer())
                    ->addSspAsset($sspAssetTransfer)
                    ->setCompanyUser($companyUserTransfer),
            );

            if (!$sspAssetCollectionResponseTransfer->getErrors()->count() && $sspAssetCollectionResponseTransfer->getSspAssets()->count()) {
                $this->addSuccessMessage(static::GLOSSARY_KEY_ASSET_CREATED);

                return $this->redirectResponseInternal(SelfServicePortalPageRouteProviderPlugin::ROUTE_NAME_ASSET_DETAILS, [
                    'reference' => $sspAssetCollectionResponseTransfer->getSspAssets()->getIterator()->current()->getReference(),
                ]);
            }

            foreach ($sspAssetCollectionResponseTransfer->getErrors() as $error) {
                $this->addErrorMessage($error->getMessageOrFail());
            }

            if (!$sspAssetCollectionResponseTransfer->getSspAssets()->count()) {
                $this->addErrorMessage(static::GLOSSARY_KEY_ASSET_CREATE_ERROR);
            }
        }

        return $this->view(
            [
                'form' => $sspAssetCreateForm->createView(),
            ],
            [],
            '@SelfServicePortal/views/asset-create/asset-create.twig',
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     *
     * @return \Spryker\Yves\Kernel\View\View|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function resellAction(Request $request): View|RedirectResponse
    {
        $assetReference = $request->get('reference');

        if (!$assetReference) {
            throw new NotFoundHttpException('Asset reference not found');
        }

        if (!$this->can(UpdateSspAssetPermissionPlugin::KEY)) {
            throw new AccessDeniedHttpException('self_service_portal.asset.access.denied');
        }

        $companyUserTransfer = $this->getFactory()
            ->getCompanyUserClient()
            ->findCompanyUser();

        if (!$companyUserTransfer) {
            $this->addErrorMessage('company.error.company_user_not_found');

            return $this->redirectResponseInternal(static::ROUTE_CUSTOMER_OVERVIEW);
        }

        // Fetch the asset
        $sspAssetTransfer = $this->getSspAssetByReference($assetReference, $companyUserTransfer);

        if (!$sspAssetTransfer) {
            throw new NotFoundHttpException('ssp_asset.error.not_found');
        }

        if (!$sspAssetTransfer->getSku()) {
            throw new AccessDeniedHttpException('Asset cannot be resold without SKU');
        }

        $sspAssetResellForm = $this->getFactory()
            ->createAssetResellForm($sspAssetTransfer)
            ->handleRequest($request);

        if ($sspAssetResellForm->isSubmitted() && $sspAssetResellForm->isValid()) {
            $sspAssetTransfer = $sspAssetResellForm->getData();

            $sspAssetCollectionResponseTransfer = $this->getClient()->updateSspAssetCollection(
                (new SspAssetCollectionRequestTransfer())->setCompanyUser($companyUserTransfer)
                    ->addSspAsset($sspAssetTransfer),
            );

            if (!$sspAssetCollectionResponseTransfer->getErrors()->count() && $sspAssetCollectionResponseTransfer->getSspAssets()->count()) {
                $this->addSuccessMessage('self_service_portal.asset.success.resell');

                return $this->redirectResponseInternal(SelfServicePortalPageRouteProviderPlugin::ROUTE_NAME_ASSET_DETAILS, [
                    'reference' => $sspAssetTransfer->getReference(),
                ]);
            }

            foreach ($sspAssetCollectionResponseTransfer->getErrors() as $error) {
                $this->addErrorMessage($error->getMessageOrFail());
            }

            if (!$sspAssetCollectionResponseTransfer->getSspAssets()->count()) {
                $this->addErrorMessage('self_service_portal.asset.error.resell');
            }
        }

        return $this->view(
            [
                'form' => $sspAssetResellForm->createView(),
                'asset' => $sspAssetTransfer,
            ],
            [],
            '@SelfServicePortal/views/asset-resell/asset-resell.twig',
        );
    }

    /**
     * @param string $reference
     * @param \Generated\Shared\Transfer\CompanyUserTransfer $companyUserTransfer
     *
     * @return \Generated\Shared\Transfer\SspAssetTransfer|null
     */
    protected function getSspAssetByReference(string $reference, $companyUserTransfer): ?SspAssetTransfer
    {
        $sspAssetCriteriaTransfer = (new SspAssetCriteriaTransfer())
            ->setSspAssetConditions(
                (new SspAssetConditionsTransfer())->addReference($reference),
            )
            ->setCompanyUser($companyUserTransfer);

        $sspAssetCollectionTransfer = $this->getClient()->getSspAssetCollection(
            $sspAssetCriteriaTransfer,
        );

        return $sspAssetCollectionTransfer->getSspAssets()->getIterator()->current();
    }

    /**
     * @param string $sku
     *
     * @return array<mixed>
     */
    protected function getProductBySku(string $sku): ?array
    {
        try {
            return $this->getFactory()
                ->getProductStorageClient()
                ->findProductConcreteStorageDataByMapping('sku', $sku, $this->getLocale());
        } catch (\Exception $e) {
            // Log error if needed
            return null;
        }
    }
}