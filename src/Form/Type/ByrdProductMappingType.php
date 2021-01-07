<?php

/*
 * This file was created by the developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusByrdShippingExportPlugin\Form\Type;

use BitBag\SyliusByrdShippingExportPlugin\Repository\ByrdProductMappingRepositoryInterface;
use Sylius\Bundle\ProductBundle\Form\Type\ProductAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ByrdProductMappingType extends AbstractType
{
    /** @var ByrdProductMappingRepositoryInterface */
    private $byrdProductMappingRepository;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        ByrdProductMappingRepositoryInterface $byrdProductMappingRepository,
        TranslatorInterface $translator
    ) {
        $this->byrdProductMappingRepository = $byrdProductMappingRepository;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product', ProductAutocompleteChoiceType::class, [
                'label' => 'bitbag_sylius_byrd_shipping_export_plugin.ui.sylius_product',
            ])
            ->add('byrdProductSku', TextType::class, [
                'label' => 'bitbag_sylius_byrd_shipping_export_plugin.ui.byrd_product_sku',
            ])
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
                $existingMapping = $this->byrdProductMappingRepository->findOneBy([
                    'product' => $event->getData()->getProduct()->getId()
                ]);
                if ($existingMapping && $existingMapping->getId() != $event->getForm()->getData()->getId()) {
                    $event->getForm()->addError(new FormError(
                        $this->translator->trans("bitbag_sylius_byrd_shipping_export_plugin.ui.form.error.product_already_in_use")
                    ));
                    return;
                }

                $existingMapping = $this->byrdProductMappingRepository->findOneBy([
                    'byrdProductSku' => $event->getData()->getByrdProductSku()
                ]);
                if ($existingMapping && $existingMapping->getId() != $event->getForm()->getData()->getId()) {
                    $event->getForm()->addError(new FormError(
                        $this->translator->trans("bitbag_sylius_byrd_shipping_export_plugin.ui.form.error.sku_already_in_use")
                    ));
                }
            })
        ;
    }
}
