<?php

namespace SprykerCommunity\Yves\SelfServicePortal\Asset\Form;

use SprykerFeature\Yves\SelfServicePortal\Asset\Form\SspAssetForm as SprykerSspAssetForm;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class SspAssetForm extends SprykerSspAssetForm
{
    /**
     * @var string
     */
    protected const FIELD_SKU = 'sku';

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array<string, mixed> $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $this->addSkuField($builder);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addSkuField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_SKU, HiddenType::class, [
            'required' => false,
        ]);

        return $this;
    }
}