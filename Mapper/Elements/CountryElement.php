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

namespace BaksDev\Drom\Board\Mapper\Elements;

use BaksDev\Drom\Board\Mapper\Products\TireProduct;
use BaksDev\Drom\Board\Repository\AllProductsWithMapper\AllProductsWithMapperResult;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CountryElement implements DromBoardElementInterface
{
    private const string ELEMENT = 'country';

    private const string LABEL = 'Страна';

    public function __construct(private readonly TranslatorInterface $Translator) {}

    public function isMapping(): bool
    {
        return true;
    }

    public function isRequired(): bool
    {
        return true;
    }

    public function getDefault(): null
    {
        return null;
    }

    public function getHelp(): ?string
    {
        return null;
    }

    public function fetchData(AllProductsWithMapperResult $data): ?string
    {
        $DromBoardPropertyMapper = $data->getDromBoardPropertyMapper();

        if(false === isset($DromBoardPropertyMapper[self::ELEMENT]))
        {
            return $this->getDefault();
        }

        return $this->Translator->trans($DromBoardPropertyMapper[self::ELEMENT], [], 'field-country');
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
