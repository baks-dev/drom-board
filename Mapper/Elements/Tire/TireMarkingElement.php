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
 *
 */

declare(strict_types=1);

namespace BaksDev\Drom\Board\Mapper\Elements\Tire;

use BaksDev\Core\Twig\CallTwigFuncExtension;
use BaksDev\Drom\Board\Mapper\Elements\DromBoardElementInterface;
use BaksDev\Drom\Board\Mapper\Products\TireProduct;
use BaksDev\Drom\Board\Repository\AllProductsWithMapper\AllProductsWithMapperResult;
use Twig\Environment;

/**
 * Маркировка шины
 *
 * Требования и рекомендации к прайс-листам шин
 * https://baza.drom.ru/help/trebovaniya_k_price_listam_po_shinam#h-11
 */
final readonly class TireMarkingElement implements DromBoardElementInterface
{
    public const string ELEMENT = 'marking';

    public const string LABEL = 'Маркировка';

    public function __construct(private Environment $Environment) {}

    public function isMapping(): bool
    {
        return false;
    }

    public function isRequired(): bool
    {
        return false;
    }

    public function getDefault(): null
    {
        return null;
    }

    public function getHelp(): null
    {
        return null;
    }

    public function fetchData(AllProductsWithMapperResult $data): string
    {
        $call = $this->Environment->getExtension(CallTwigFuncExtension::class);

        $strOffer = '';

        /** Множественный вариант */
        $variation = $call->call(
            $this->Environment,
            $data->getProductVariationValue(),
            $data->getProductVariationReference().'_render',
        );

        $strOffer .= $variation ? trim($variation) : '';

        /** Модификация множественного варианта */
        $modification = $call->call(
            $this->Environment,
            $data->getProductModificationValue(),
            $data->getProductModificationReference().'_render',
        );

        $strOffer .= $modification ? trim($modification) : '';

        /** Торговое предложение */
        $offer = $call->call(
            $this->Environment,
            $data->getProductOfferValue(),
            $data->getProductOfferReference().'_render',
        );

        $strOffer .= $modification ? trim($offer) : '';
        $strOffer .= $data->getProductOfferPostfix() ? ' '.$data->getProductOfferPostfix() : '';
        $strOffer .= $data->getProductVariationPostfix() ? ' '.$data->getProductVariationPostfix() : '';
        $strOffer .= $data->getProductModificationPostfix() ? ' '.$data->getProductModificationPostfix() : '';

        return $strOffer;
    }

    public function element(): string
    {
        return self::ELEMENT;
    }

    public function label(): string
    {
        return self::LABEL;
    }

    public function getProduct(): string
    {
        return TireProduct::class;
    }
}
