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

namespace BaksDev\Drom\Board\Mapper\Elements\Tire;

use BaksDev\Drom\Board\Mapper\Products\TireProduct;
use BaksDev\Drom\Board\Mapper\Elements\DromBoardElementInterface;
use BaksDev\Drom\Board\Repository\AllProductsWithMapper\AllProductsWithMapperResult;

/**
 * Количество шт. в комплекте
 *
 * Требования и рекомендации к прайс-листам шин
 * https://baza.drom.ru/help/trebovaniya_k_price_listam_po_shinam#h-11
 */
final readonly class TireQuantityElement implements DromBoardElementInterface
{
    private const string ELEMENT = 'inSet';

    private const string LABEL = 'Количество покрышек в комплекте';

    public function isMapping(): false
    {
        return false;
    }

    public function isRequired(): true
    {
        return true;
    }

    public function getDefault(): string
    {
        return '1';
    }

    public function getHelp(): null
    {
        return null;
    }

    public function fetchData(AllProductsWithMapperResult $data): string
    {
        /** Если параметр Количество товаров в объявлении НЕ УСТАНОВЛЕН - значит в объявлении 1 товар */
        $quantity = $this->getDefault();

        /**
         * Если параметр Количество товаров в объявлении УСТАНОВЛЕН и не равен 1 - значит в объявлении количество
         * товаров из KIT
         */
        if($data->getDromKitValue() > 1)
        {
            $quantity = sprintf('%s', $data->getDromKitValue());
        }

        return $quantity;
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
