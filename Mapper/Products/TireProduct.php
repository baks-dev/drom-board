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

namespace BaksDev\Drom\Board\Mapper\Products;

use BaksDev\Drom\Board\Mapper\Elements\DromBoardElementInterface;
use Exception;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class TireProduct implements DromBoardProductInterface
{
    private const string PRODUCT_CATEGORY = 'Шины';

    public function __construct(#[AutowireIterator('baks.drom.board.mapper.elements')] private iterable $elements) {}

    /** Получаем название категории Drom */
    public function getProductCategory(): string
    {
        return self::PRODUCT_CATEGORY;
    }

    /** Получаем массив всех элементов */
    public function getElements(): array
    {
        $passengerTireElements = null;

        /** @var DromBoardElementInterface $element */
        foreach($this->elements as $element)
        {
            if(null === $element->getProduct() || $element->getProduct() === self::class)
            {
                $passengerTireElements[] = $element;
            }
        }

        if(null === $passengerTireElements)
        {
            throw new Exception('Не найдено ни одного элемента');
        }

        return $passengerTireElements;
    }

    /** Получаем элемент по его названию */
    public function getElement(string $elementName): ?DromBoardElementInterface
    {
        /** @var DromBoardElementInterface $element */
        foreach($this->elements as $element)
        {
            if($element->element() === $elementName)
            {
                if($element->getProduct() === null || $element->getProduct() === self::class)
                {
                    return $element;
                }
            }
        }

        throw new Exception('Не найден элемент');
    }

    public function isEqual(string $productCategory): bool
    {
        return $productCategory === 'Шины';
    }
}
