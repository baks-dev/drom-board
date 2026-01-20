<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Drom\Board\Mapper\Elements;

use BaksDev\Drom\Board\Repository\AllProductsWithMapper\AllProductsWithMapperResult;
use BaksDev\Reference\Money\Type\Money;

/**
 * Цена в рублях — целое число
 *
 * Элемент обязателен для всех продуктов Drom
 */
class PriceElement implements DromBoardElementInterface
{
    private const string ELEMENT = 'price';

    private const string LABEL = 'Цена';

    public function isMapping(): bool
    {
        return false;
    }

    public function isRequired(): true
    {
        return true;
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
        $price = $data->getProductPrice();

        if(false === $price)
        {
            return 'По запросу';
        }

        /**
         * Если параметр Количество товаров в объявлении УСТАНОВЛЕН и не равен 1 - объявление дублируется, цена
         * умножается на значение drom_kit_value
         */

        if($data->getDromKitValue() > 1)
        {
            $priceWithKit = $price->getValue(true) * $data->getDromKitValue();
            $price = new Money($priceWithKit, true);
            $price->applyString(($data->getDromKitValue() * -0.1).'%'); // Делаем скидку на комплект резины
        }

        return (string) $price->getRoundValue(10);
    }

    public function element(): string
    {
        return self::ELEMENT;
    }

    public function label(): string
    {
        return self::LABEL;
    }

    public function getProduct(): null
    {
        return null;
    }
}
