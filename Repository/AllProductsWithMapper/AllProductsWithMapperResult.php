<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Drom\Board\Repository\AllProductsWithMapper;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Offers\Id\CategoryProductOffersUid;
use BaksDev\Products\Category\Type\Offers\Modification\CategoryProductModificationUid;
use BaksDev\Products\Category\Type\Offers\Variation\CategoryProductVariationUid;
use BaksDev\Products\Product\Repository\ProductPriceResultInterface;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/** @see AllProductsWithDromMapperRepository */
#[Exclude]
final class AllProductsWithMapperResult implements ProductPriceResultInterface
{
    private ?array $drom_board_mapper_decode = null;

    public function __construct(
        private readonly string $id,
        private readonly string $event,
        private readonly ?int $drom_kit_value,
        private readonly string $drom_profile_percent,
        private readonly ?string $drom_product_id,
        private readonly string $product_date_begin,
        private readonly ?string $product_date_over,
        private readonly string $product_name,
        private readonly ?string $product_description,
        private readonly ?string $product_offer_id,
        private readonly ?string $product_offer_const,
        private readonly ?string $product_offer_value,
        private readonly ?string $product_offer_postfix,
        private readonly ?string $offer_section_field_uid,
        private readonly ?string $product_offer_reference,
        private readonly ?string $product_variation_id,
        private readonly ?string $product_variation_const,
        private readonly ?string $product_variation_value,
        private readonly ?string $product_variation_postfix,
        private readonly ?string $product_variation_reference,
        private readonly ?string $variation_section_field_uid,
        private readonly ?string $product_modification_id,
        private readonly ?string $product_modification_const,
        private readonly ?string $product_modification_value,
        private readonly ?string $product_modification_postfix,
        private readonly ?string $product_modification_reference,
        private readonly ?string $modification_section_field_uid,
        private readonly string $product_article,
        private readonly bool $category_active,
        private readonly string $product_category,
        private readonly ?int $product_price,
        private readonly ?string $product_currency,
        private readonly ?string $product_quantity,
        private readonly ?string $product_images,
        private readonly string $drom_board_mapper_category_id,
        private readonly string $drom_board_drom_category,
        private readonly string $drom_board_mapper,
        private readonly ?string $drom_product_description,
        private readonly ?string $drom_product_images,


        private readonly string|null $project_discount = null,

        private readonly ?bool $promotion_active = null,
        private readonly string|null $promotion_price = null,
    ) {}

    public function getProductId(): ProductUid
    {
        return new ProductUid($this->id);
    }

    public function getProductEvent(): ProductEventUid
    {
        return new ProductEventUid($this->event);
    }

    public function getDromProductId(): string
    {
        return $this->drom_product_id ?: 'undefined';
    }

    public function getProductDateBegin(): string
    {
        return $this->product_date_begin;
    }

    public function getProductDateOver(): ?string
    {
        return $this->product_date_over;
    }

    public function getProductName(): string
    {
        return $this->product_name;
    }

    public function getProductDescription(): ?string
    {
        return $this->product_description;
    }

    public function getProductOfferId(): ?ProductOfferUid
    {
        return is_null($this->product_offer_id)
            ? null
            : new ProductOfferUid($this->product_offer_id);
    }

    public function getProductOfferConst(): ?ProductOfferConst
    {
        return is_null($this->product_offer_const)
            ? null
            : new ProductOfferConst($this->product_offer_const);
    }

    public function getProductOfferValue(): ?string
    {
        return $this->product_offer_value;
    }

    public function getProductOfferPostfix(): ?string
    {
        return $this->product_offer_postfix;
    }

    public function getOfferSectionFieldUid(): ?CategoryProductOffersUid
    {
        return is_null($this->offer_section_field_uid)
            ? null
            : new CategoryProductOffersUid($this->product_offer_const);
    }

    public function getProductOfferReference(): ?string
    {
        return $this->product_offer_reference;
    }

    public function getProductVariationId(): ?ProductVariationUid
    {
        return is_null($this->product_variation_id)
            ? null
            : new ProductVariationUid($this->product_variation_id);
    }

    public function getProductVariationConst(): ?ProductVariationConst
    {
        return is_null($this->product_variation_const)
            ? null
            : new ProductVariationConst($this->product_variation_const);
    }

    public function getProductVariationValue(): ?string
    {
        return $this->product_variation_value;
    }

    public function getProductVariationPostfix(): ?string
    {
        return $this->product_variation_postfix;
    }

    public function getVariationSectionFieldUid(): ?CategoryProductVariationUid
    {
        return is_null($this->variation_section_field_uid)
            ? null
            : new CategoryProductVariationUid($this->variation_section_field_uid);
    }

    public function getProductModificationId(): ?string
    {
        return $this->product_modification_id;
    }

    public function getProductModificationConst(): ?ProductModificationUid
    {
        return is_null($this->product_modification_const)
            ? null
            : new ProductModificationUid($this->product_modification_const);
    }

    public function getProductModificationValue(): ?string
    {
        return $this->product_modification_value;
    }

    public function getProductModificationPostfix(): ?string
    {
        return $this->product_modification_postfix;
    }

    public function getModificationSectionFieldUid(): ?CategoryProductModificationUid
    {
        return is_null($this->modification_section_field_uid)
            ? null
            : new CategoryProductModificationUid($this->modification_section_field_uid);
    }

    public function getProductArticle(): string
    {
        return $this->product_article;
    }

    public function isCategoryActive(): bool
    {
        return $this->category_active;
    }

    public function getProductCategory(): string
    {
        return $this->product_category;
    }

    public function getProductPrice(): Money|false
    {

        if(empty($this->product_price))
        {
            return false;
        }

        $price = new Money($this->product_price, true);

        /** Акция/наценка магазина (promotion) */
        if(false === empty($this->promotion_price) && true === $this->promotion_active)
        {
            $price->applyString($this->promotion_price);
        }

        /** Торговая наценка/скидка профиля магазина */
        if(false === empty($this->project_discount))
        {
            $price->applyString($this->project_discount);
        }

        /** Наценка/скидка токена профиля магазина */
        if(false === empty($this->drom_profile_percent))
        {
            $price->applyString($this->drom_profile_percent);
        }

        return $price;
    }

    public function getProductCurrency(): Currency
    {
        return new Currency($this->product_currency);
    }

    public function getProductQuantity(): int
    {

        if(empty($this->product_quantity))
        {
            return 0;
        }

        if(false === json_validate($this->product_quantity))
        {
            return 0;
        }

        $decode = json_decode($this->product_quantity, false, 512, JSON_THROW_ON_ERROR);

        $quantity = 0;

        foreach($decode as $item)
        {
            $quantity += $item->total;
            $quantity -= $item->reserve;
        }

        return max($quantity, 0);
    }

    public function getProductImages(): array|null
    {
        if(is_null($this->product_images))
        {
            return null;
        }

        if(false === json_validate($this->product_images))
        {
            return null;
        }

        $images = json_decode($this->product_images, false, 512, JSON_THROW_ON_ERROR);

        if(null === current($images))
        {
            return null;
        }

        return $images;
    }

    public function getDromBoardMapperCategoryId(): CategoryProductUid
    {
        return new CategoryProductUid($this->drom_board_mapper_category_id);
    }

    public function getDromBoardDromCategory(): string
    {
        return $this->drom_board_drom_category;
    }

    public function getDromProductDescription(): ?string
    {
        return $this->drom_product_description;
    }

    public function getDromProductImages(): array|null
    {
        if(is_null($this->drom_product_images))
        {
            return null;
        }

        if(false === json_validate($this->drom_product_images))
        {
            return null;
        }

        $images = json_decode($this->drom_product_images, false, 512, JSON_THROW_ON_ERROR);

        if(null === current($images))
        {
            return null;
        }

        return $images;
    }

    public function getDromKitValue(): ?int
    {
        return $this->drom_kit_value ?? 0;
    }

    public function getDromProfilePercent(): string
    {
        return $this->drom_profile_percent;
    }

    /** Property Drom Board Mapper */
    public function getDromBoardPropertyMapper(): ?array
    {
        if(true === is_null($this->drom_board_mapper_decode))
        {
            if(is_null($this->drom_board_mapper))
            {
                return null;
            }

            if(false === json_validate($this->drom_board_mapper))
            {
                return null;
            }

            /**
             * @var array{'value': string, 'element': string } $data
             */
            $data = json_decode($this->drom_board_mapper, false, 512, JSON_THROW_ON_ERROR);

            if(null === current($data))
            {
                return null;
            }

            $this->drom_board_mapper_decode = array_column($data, 'value', 'element');
        }

        return $this->drom_board_mapper_decode;
    }

    public function getProductOldPrice(): Money|false
    {
        return false;
    }

    public function getProductModificationReference(): ?string
    {
        return $this->product_modification_reference;
    }

    public function getProductVariationReference(): ?string
    {
        return $this->product_variation_reference;
    }
}
