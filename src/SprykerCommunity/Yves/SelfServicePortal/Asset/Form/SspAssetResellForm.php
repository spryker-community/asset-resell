<?php

namespace SprykerCommunity\Yves\SelfServicePortal\Asset\Form;

use Generated\Shared\Transfer\SspAssetTransfer;
use Spryker\Yves\Kernel\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class SspAssetResellForm extends AbstractType
{
    /**
     * @var string
     */
    protected const FIELD_ASSET_CONDITION = 'asset_condition';

    /**
     * @var string
     */
    protected const FIELD_PRICE = 'price';

    /**
     * @var array<string>
     */
    protected const CONDITION_OPTIONS = [
        'Very good' => 'very_good',
        'Good' => 'good',
        'Poor' => 'poor',
    ];

    public function getBlockPrefix(): string
    {
        return 'assetResellForm';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SspAssetTransfer::class,
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array<string, mixed> $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this
            ->addConditionField($builder)
            ->addPriceField($builder);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addConditionField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_ASSET_CONDITION, ChoiceType::class, [
            'label' => 'self_service_portal.asset.resell.condition',
            'required' => true,
            'choices' => static::CONDITION_OPTIONS,
            'placeholder' => 'self_service_portal.asset.resell.condition.placeholder',
            'constraints' => [
                new NotBlank(),
            ],
            'attr' => [
                'data-qa' => 'ssp-asset-condition-field',
            ],
        ]);

        return $this;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     *
     * @return $this
     */
    protected function addPriceField(FormBuilderInterface $builder)
    {
        $builder->add(static::FIELD_PRICE, NumberType::class, [
            'label' => 'self_service_portal.asset.resell.price',
            'required' => true,
            'scale' => 2,
            'constraints' => [
                new NotBlank(),
                new PositiveOrZero(),
            ],
            'attr' => [
                'step' => '0.01',
                'min' => '0',
                'placeholder' => '0.00',
                'data-qa' => 'ssp-asset-price-field',
            ],
        ]);

        return $this;
    }
}