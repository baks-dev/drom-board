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

use BaksDev\Drom\Board\Entity\DromBoard;
use BaksDev\Drom\Board\Entity\Element\DromBoardMapperElement;
use BaksDev\Drom\Board\Entity\Event\DromBoardEvent;
use BaksDev\Drom\Entity\DromToken;
use BaksDev\Drom\Entity\Kit\DromTokenKit;
use BaksDev\Drom\Entity\Percent\DromTokenPercent;
use BaksDev\Drom\Entity\Profile\DromTokenProfile;
use BaksDev\Drom\Products\Entity\DromProduct;
use BaksDev\Drom\Products\Entity\Images\DromProductImage;
use BaksDev\Drom\Products\Entity\Kit\DromProductKit;
use BaksDev\Drom\Products\Entity\Profile\DromProductProfile;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Info\CategoryProductInfo;
use BaksDev\Products\Category\Entity\Offers\CategoryProductOffers;
use BaksDev\Products\Category\Entity\Offers\Variation\CategoryProductVariation;
use BaksDev\Products\Category\Entity\Offers\Variation\Modification\CategoryProductModification;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use BaksDev\Products\Product\Entity\Active\ProductActive;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Description\ProductDescription;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\Price\ProductOfferPrice;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Quantity\ProductOfferQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Price\ProductModificationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Quantity\ProductModificationQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Price\ProductVariationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Offers\Variation\Quantity\ProductVariationQuantity;
use BaksDev\Products\Product\Entity\Price\ProductPrice;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\ProductInvariable;
use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Promotion\BaksDevProductsPromotionBundle;
use BaksDev\Products\Promotion\Entity\Event\Invariable\ProductPromotionInvariable;
use BaksDev\Products\Promotion\Entity\Event\Period\ProductPromotionPeriod;
use BaksDev\Products\Promotion\Entity\Event\Price\ProductPromotionPrice;
use BaksDev\Products\Promotion\Entity\ProductPromotion;
use BaksDev\Products\Stocks\BaksDevProductsStocksBundle;
use BaksDev\Products\Stocks\Entity\Total\ProductStockTotal;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Discount\UserProfileDiscount;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use Generator;
use InvalidArgumentException;

final class AllProductsWithMapperRepository implements AllProductsWithMapperInterface
{
    private UserProfileUid|false $profile = false;

    private ProductUid|false $product = false;

    private ProductOfferConst|false $offerConst = false;

    private ProductVariationConst|false $variationConst = false;

    private ProductModificationConst|false $modificationConst = false;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function forProfile(UserProfile|UserProfileUid $profile): self
    {
        if($profile instanceof UserProfile)
        {
            $profile = $profile->getId();
        }

        $this->profile = $profile;

        return $this;
    }

    public function forProduct(ProductUid $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function forOfferConst(ProductOfferConst $offerConst): self
    {
        $this->offerConst = $offerConst;
        return $this;
    }

    public function forVariationConst(ProductVariationConst $variationConst): self
    {
        $this->variationConst = $variationConst;
        return $this;
    }

    public function forModificationConst(ProductModificationConst $modificationConst): self
    {
        $this->modificationConst = $modificationConst;
        return $this;
    }


    /**
     * Метод получает массив элементов продукции с соотношением свойств
     * @return Generator<int, AllProductsWithMapperResult>|false
     * */
    public function findAll(): Generator|false
    {
        if($this->profile === false)
        {
            throw new InvalidArgumentException('Invalid Argument profile');
        }

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('product.id')
            ->addSelect('product.event')
            ->from(Product::class, 'product');

        if($this->product instanceof ProductUid)
        {
            $dbal
                ->where('product.id = :product')
                ->setParameter('product', $this->product, ProductUid::TYPE);
        }

        $dbal
            ->join(
                'product',
                DromTokenProfile::class,
                'drom_token_profile',
                'drom_token_profile.value = :profile'
            )
            ->setParameter(
                key: 'profile',
                value: $this->profile,
                type: UserProfileUid::TYPE
            );


        /** Проверка, есть ли соответствующий профиль */
        $dbal->join(
            'drom_token_profile',
            DromToken::class,
            'drom_token',
            'drom_token.event = drom_token_profile.event'
        );

        $dbal
            ->addSelect('drom_kit.value AS drom_kit_value')
            ->leftJoin(
                'drom_token',
                DromTokenKit::class,
                'drom_kit',
                'drom_kit.event = drom_token.event'
            );

        $dbal->join(
            'drom_token',
            UserProfileInfo::class,
            'info',
            '
                info.profile = :profile AND
                info.status = :status',
        )
            ->setParameter(
                'status',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE
            );

        $dbal
            ->addSelect('drom_token_percent.value AS drom_profile_percent')
            ->leftJoin(
                'drom_token',
                DromTokenPercent::class,
                'drom_token_percent',
                'drom_token_percent.event = drom_token.event',
            );

        $dbal->leftJoin(
            'product',
            ProductEvent::class,
            'product_event',
            'product_event.id = product.event'
        );


        /** Получаем только на активные продукты */
        $dbal
            ->addSelect('product_active.active_from AS product_date_begin')
            ->addSelect('product_active.active_to AS product_date_over')
            ->join(
                'product',
                ProductActive::class,
                'product_active',
                '
                    product_active.event = product.event AND 
                    product_active.active IS TRUE'
            );

        $dbal
            ->leftJoin(
                'product_event',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id'
            );


        /** Получаем название с учетом настроек локализации */
        $dbal
            ->addSelect('product_trans.name AS product_name')
            ->leftJoin(
                'product_event',
                ProductTrans::class,
                'product_trans',
                'product_trans.event = product_event.id AND product_trans.local = :local'
            );

        $dbal
            ->addSelect('product_desc.preview AS product_description')
            ->leftJoin(
                'product_event',
                ProductDescription::class,
                'product_desc',
                'product_desc.event = product_event.id AND product_desc.device = :device '
            )
            ->setParameter('device', 'pc');


        /** Торговое предложение */
        $dbal
            ->addSelect('product_offer.id as product_offer_id')
            ->addSelect('product_offer.const as product_offer_const')
            ->addSelect('product_offer.value as product_offer_value')
            ->addSelect('product_offer.postfix as product_offer_postfix')
            ->addSelect('product_offer.category_offer as offer_section_field_uid')
            ->leftJoin(
                'product_event',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product_event.id'
                .($this->offerConst instanceof ProductOfferConst ? ' AND product_offer.const = :offer' : '')
            )
            ->setParameter('offer', $this->offerConst, ProductOfferConst::TYPE);


        /** Тип торгового предложения */
        $dbal
            ->addSelect('category_offer.reference as product_offer_reference')
            ->leftJoin(
                'product_offer',
                CategoryProductOffers::class,
                'category_offer',
                'category_offer.id = product_offer.category_offer'
            );

        /** Множественные варианты торгового предложения */
        $dbal
            ->addSelect('product_variation.id as product_variation_id')
            ->addSelect('product_variation.const as product_variation_const')
            ->addSelect('product_variation.value as product_variation_value')
            ->addSelect('product_variation.postfix as product_variation_postfix')
            ->addSelect('product_variation.category_variation as variation_section_field_uid')
            ->leftJoin(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                'product_variation.offer = product_offer.id'
                .($this->variationConst instanceof ProductVariationConst ? ' AND product_variation.const = :variation' : '')
            )
            ->setParameter('variation', $this->variationConst, ProductVariationConst::TYPE);


        /** Тип множественного варианта торгового предложения */
        $dbal
            ->addSelect('category_variation.reference as product_variation_reference')
            ->leftJoin(
                'product_variation',
                CategoryProductVariation::class,
                'category_variation',
                'category_variation.id = product_variation.category_variation'
            );


        /** Модификация множественного варианта */
        $dbal
            ->addSelect('product_modification.id as product_modification_id')
            ->addSelect('product_modification.const as product_modification_const')
            ->addSelect('product_modification.value as product_modification_value')
            ->addSelect('product_modification.postfix as product_modification_postfix')
            ->addSelect('product_modification.category_modification as modification_section_field_uid')
            ->leftJoin(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id'
                .($this->modificationConst instanceof ProductModificationConst ? ' AND product_modification.const = :modification' : '')
            )
            ->setParameter('modification', $this->modificationConst, ProductModificationConst::TYPE);


        /** Тип множественного варианта торгового предложения */
        $dbal
            ->addSelect('category_modification.reference as product_modification_reference')
            ->leftJoin(
                'product_modification',
                CategoryProductModification::class,
                'category_modification',
                'category_modification.id = product_modification.category_modification'
            );


        /** Артикул продукта */
        $dbal->addSelect('
            COALESCE(
                product_modification.article, 
                product_variation.article, 
                product_offer.article, 
                product_info.article
            ) AS product_article
		');


        /** Категория */
        $dbal
            ->leftJoin(
                'product_event',
                ProductCategory::class,
                'product_category',
                'product_category.event = product_event.id AND product_category.root = true'
            );

        $dbal->join(
            'product_category',
            CategoryProduct::class,
            'category',
            'category.id = product_category.category'
        );


        /** Получаем только на активные категории */
        $dbal
            ->addSelect('category_info.active as category_active')
            ->join(
                'product_category',
                CategoryProductInfo::class,
                'category_info',
                '
                    category.event = category_info.event AND
                    category_info.active IS TRUE'
            );

        $dbal
            ->addSelect('category_trans.name AS product_category')
            ->leftJoin(
                'category',
                CategoryProductTrans::class,
                'category_trans',
                'category_trans.event = category.event AND category_trans.local = :local'
            );


        /** Базовая Цена товара */
        $dbal->leftJoin(
            'product',
            ProductPrice::class,
            'product_price',
            'product_price.event = product.event'
        )
            ->addGroupBy('product_price.reserve');


        /** Цена торгового предложения */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferPrice::class,
            'product_offer_price',
            'product_offer_price.offer = product_offer.id'
        );


        /** Цена множественного варианта */
        $dbal->leftJoin(
            'product_variation',
            ProductVariationPrice::class,
            'product_variation_price',
            'product_variation_price.variation = product_variation.id'
        );


        /** Цена модификации множественного варианта */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationPrice::class,
            'product_modification_price',
            'product_modification_price.modification = product_modification.id'
        );


        /** Стоимость продукта */
        $dbal->addSelect(
            '
			CASE
			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 
			   THEN product_modification_price.price
			   
			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0 
			   THEN product_variation_price.price
			   
			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 
			   THEN product_offer_price.price
			   
			   WHEN product_price.price IS NOT NULL AND product_price.price > 0 
			   THEN product_price.price
			   
			   ELSE NULL
			END AS product_price'
        );


        /** Валюта продукта */
        $dbal->addSelect(
            '
			CASE
			
			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 
			   THEN product_modification_price.currency
			   
			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0 
			   THEN product_variation_price.currency
			   
			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 
			   THEN product_offer_price.currency
			   
			   WHEN product_price.price IS NOT NULL AND product_price.price > 0 
			   THEN product_price.currency
			   
			   ELSE NULL
			   
			END AS product_currency'
        );


        /** Наличие продукции на складе (если подключен модуль складского учета и передан идентификатор профиля) */
        if(
            true === ($this->profile instanceof UserProfileUid) &&
            class_exists(BaksDevProductsStocksBundle::class)
        )
        {
            $dbal
                ->addSelect("JSON_AGG ( 
                        DISTINCT JSONB_BUILD_OBJECT (
                            'total', stock.total, 
                            'reserve', stock.reserve 
                        )) FILTER (WHERE stock.total > stock.reserve)
            
                        AS product_quantity",
                )
                ->leftJoin(
                    'product_modification',
                    ProductStockTotal::class,
                    'stock',
                    '
                    stock.profile = :profile AND
                    stock.product = product.id 
                    
                    AND
                        
                        CASE 
                            WHEN product_offer.const IS NOT NULL 
                            THEN stock.offer = product_offer.const
                            ELSE stock.offer IS NULL
                        END
                            
                    AND 
                    
                        CASE
                            WHEN product_variation.const IS NOT NULL 
                            THEN stock.variation = product_variation.const
                            ELSE stock.variation IS NULL
                        END
                        
                    AND
                    
                        CASE
                            WHEN product_modification.const IS NOT NULL 
                            THEN stock.modification = product_modification.const
                            ELSE stock.modification IS NULL
                        END
                ',
                )
                ->setParameter(
                    'profile',
                    $this->profile,
                    UserProfileUid::TYPE,
                );
        }
        else
        {
            /* Наличие и резерв торгового предложения */
            $dbal->leftJoin(
                'product_offer',
                ProductOfferQuantity::class,
                'product_offer_quantity',
                'product_offer_quantity.offer = product_offer.id',
            );

            /* Наличие и резерв множественного варианта */
            $dbal->leftJoin(
                'product_variation',
                ProductVariationQuantity::class,
                'product_variation_quantity',
                'product_variation_quantity.variation = product_variation.id',
            );

            /* Наличие и резерв модификации множественного варианта */
            $dbal->leftJoin(
                'product_modification',
                ProductModificationQuantity::class,
                'product_modification_quantity',
                'product_modification_quantity.modification = product_modification.id',
            );

            $dbal
                ->addSelect("JSON_AGG (
                    DISTINCT JSONB_BUILD_OBJECT (
           
                        'total', COALESCE(
                                        product_modification_quantity.quantity, 
                                        product_variation_quantity.quantity, 
                                        product_offer_quantity.quantity, 
                                        product_price.quantity,
                                        0
                                    ), 
                            
                        'reserve', COALESCE(
                                        product_modification_quantity.reserve, 
                                        product_variation_quantity.reserve, 
                                        product_offer_quantity.reserve, 
                                        product_price.reserve,
                                        0
                                    )
                    )
                    
                ) AS product_quantity",
            );
        }
        $dbal->addSelect('NULL AS product_images');


        /** Общая скидка (наценка) из профиля магазина */
        if(true === $dbal->bindProjectProfile())
        {
            $dbal
                ->join(
                    'product',
                    UserProfile::class,
                    'project_profile',
                    '
                        project_profile.id = :'.$dbal::PROJECT_PROFILE_KEY,
                );

            $dbal
                ->addSelect('project_profile_discount.value AS project_discount')
                ->leftJoin(
                    'project_profile',
                    UserProfileDiscount::class,
                    'project_profile_discount',
                    '
                        project_profile_discount.event = project_profile.event',
                );
        }


        /** ProductInvariable */
        $dbal
            ->leftJoin(
                'product_modification',
                ProductInvariable::class,
                'product_invariable',
                '
                    product_invariable.product = product.id AND 
                    
                    (
                        (product_offer.const IS NOT NULL AND product_invariable.offer = product_offer.const) OR 
                        (product_offer.const IS NULL AND product_invariable.offer IS NULL)
                    )
                    
                    AND
                     
                    (
                        (product_variation.const IS NOT NULL AND product_invariable.variation = product_variation.const) OR 
                        (product_variation.const IS NULL AND product_invariable.variation IS NULL)
                    )
                     
                   AND
                   
                   (
                        (product_modification.const IS NOT NULL AND product_invariable.modification = product_modification.const) OR 
                        (product_modification.const IS NULL AND product_invariable.modification IS NULL)
                   )
            ');

        /** ProductsPromotion */
        if(true === class_exists(BaksDevProductsPromotionBundle::class) && true === $dbal->isProjectProfile())
        {
            $dbal
                ->leftJoin(
                    'product_invariable',
                    ProductPromotionInvariable::class,
                    'product_promotion_invariable',
                    '
                        product_promotion_invariable.product = product_invariable.id
                        AND product_promotion_invariable.profile = :'.$dbal::PROJECT_PROFILE_KEY,
                );

            $dbal
                ->leftJoin(
                    'product_promotion_invariable',
                    ProductPromotion::class,
                    'product_promotion',
                    'product_promotion.id = product_promotion_invariable.main',
                );

            $dbal
                ->addSelect('product_promotion_price.value AS promotion_price')
                ->leftJoin(
                    'product_promotion',
                    ProductPromotionPrice::class,
                    'product_promotion_price',
                    'product_promotion_price.event = product_promotion.event',
                );

            $dbal
                ->addSelect('
                CASE
                    WHEN 
                        CURRENT_DATE >= product_promotion_period.date_start
                        AND
                         (
                            product_promotion_period.date_end IS NULL OR
                            CURRENT_DATE <= product_promotion_period.date_end
                         )
                    THEN true
                    ELSE false
                END AS promotion_active
            ')
                ->leftJoin(
                    'product_promotion',
                    ProductPromotionPeriod::class,
                    'product_promotion_period',
                    '
                        product_promotion_period.event = product_promotion.event',
                );
        }


        /** Drom mapper */

        /** Категория, для которой создан маппер. Для каждой карточки */
        $dbal
            ->addSelect('drom_board.id AS drom_board_mapper_category_id')
            ->leftJoin(
                'product_category',
                DromBoard::class,
                'drom_board',
                'drom_board.id = product_category.category'
            );


        /** Название категории в Drom из активного события маппера. Для каждой карточки */
        $dbal
            ->addSelect('drom_board_event.drom AS drom_board_drom_category')
            ->leftJoin(
                'drom_board',
                DromBoardEvent::class,
                'drom_board_event',
                'drom_board_event.id = drom_board.event'
            );

        $dbal
            ->leftJoin(
                'drom_board',
                DromBoardMapperElement::class,
                'drom_mapper',
                'drom_mapper.event = drom_board.event'
            );

        /** Получаем значение из СВОЙСТВ товара */
        $dbal
            ->leftJoin(
                'drom_mapper',
                ProductProperty::class,
                'product_property',
                '
                product_property.event = product.event AND 
                product_property.field = drom_mapper.product_field'
            );

        /** Получаем значение из торговых предложений */
        $dbal
            ->leftJoin(
                'drom_mapper',
                ProductOffer::class,
                'product_offer_params',
                '
                    product_offer_params.id = product_offer.id AND  
                    product_offer_params.category_offer = drom_mapper.product_field'
            );

        /** Получаем значение из вариантов модификации множественного варианта */
        $dbal
            ->leftJoin(
                'drom_mapper',
                ProductVariation::class,
                'product_variation_params',
                '
                    product_variation_params.id = product_variation.id AND 
                    product_variation_params.category_variation = drom_mapper.product_field'
            );

        /** Получаем значение из модификаций множественного варианта */
        $dbal
            ->leftJoin(
                'drom_mapper',
                ProductModification::class,
                'product_modification_params',
                '
                    product_modification_params.id = product_modification.id AND 
                    product_modification_params.category_modification = drom_mapper.product_field'
            );


        $dbal->addSelect(
            "JSON_AGG
			(
                DISTINCT
					JSONB_BUILD_OBJECT
                        (
                            'element', drom_mapper.element,
                
                            'value', 
                                (CASE
                                   WHEN product_property.value IS NOT NULL THEN product_property.value
                                   WHEN product_offer_params.value IS NOT NULL THEN product_offer_params.value
                                   WHEN product_modification_params.value IS NOT NULL THEN product_modification_params.value
                                   WHEN product_variation_params.value IS NOT NULL THEN product_variation_params.value
                                   WHEN drom_mapper.def IS NOT NULL THEN drom_mapper.def
                                   ELSE NULL
                                END)
                        )
			) 
			AS drom_board_mapper"
        );


        /** Продукт Drom */
        $dbal
            ->addSelect('drom_product.id as drom_product_id')
            ->addSelect('drom_product.description as drom_product_description')
            ->leftJoin(
                'product_modification',
                DromProduct::class,
                'drom_product',
                '
                
                drom_product.product = product.id 

                AND
                        
                    CASE 
                        WHEN product_offer.const IS NOT NULL 
                        THEN drom_product.offer = product_offer.const
                        ELSE drom_product.offer IS NULL
                    END
                        
                AND 
                
                    CASE
                        WHEN product_variation.const IS NOT NULL 
                        THEN drom_product.variation = product_variation.const
                        ELSE drom_product.variation IS NULL
                    END
                    
                AND
                
                    CASE
                        WHEN product_modification.const IS NOT NULL 
                        THEN drom_product.modification = product_modification.const
                        ELSE drom_product.modification IS NULL
                    END
            ');


        /** Продукт Drom по профилю бизнес-пользователя */
        $dbal
            ->join(
                'drom_product',
                DromProductProfile::class,
                'drom_product_profile',
                'drom_product_profile.drom = drom_product.id AND
                drom_product_profile.value = drom_token_profile.value',
            );


        /** Количество в объявлении Drom */
        $dbal->leftJoin(
            'drom_product',
            DromProductKit::class,
            'drom_product_kit',
            '
                drom_product_kit.drom = drom_product.id
                AND drom_product_kit.value = drom_kit.value
            ',
        );


        /** Изображения Drom, используем только главное */
        $dbal->join(
            'drom_product_kit',
            DromProductImage::class,
            'drom_product_images',
            'drom_product_images.drom = drom_product.id AND drom_product_images.root = true',
        );

        $dbal->addSelect(
            "JSON_AGG
            (DISTINCT
                CASE
                    WHEN drom_product_images.name IS NOT NULL THEN JSONB_BUILD_OBJECT
                    (
                        'img_root', drom_product_images.root,
                        'img', CONCAT ( '/upload/".$dbal->table(DromProductImage::class)."' , '/', drom_product_images.name),
                        'img_ext', drom_product_images.ext,
                        'img_cdn', drom_product_images.cdn
                    )
                    ELSE NULL
                END
            ) as drom_product_images
            "
        );

        $dbal->allGroupByExclude();

        $dbal->andWhere('(drom_board.id IS NOT NULL AND drom_board_event.category IS NOT NULL)');


        /** Только заказы, у которых указана стоимость */
        $dbal->andWhere('
            (
                CASE
                   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0
                   THEN product_modification_price.price

                   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0
                   THEN product_variation_price.price

                   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0
                   THEN product_offer_price.price

                   WHEN product_price.price IS NOT NULL AND product_price.price > 0
                   THEN product_price.price

                   ELSE 0
                END
            ) > 0
        ');

        $dbal->enableCache('drom-board', '1 day');

        $result = $dbal->fetchAllHydrate(AllProductsWithMapperResult::class);

        return (true === $result->valid()) ? $result : false;
    }
}
