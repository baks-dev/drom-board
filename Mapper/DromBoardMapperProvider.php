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

namespace BaksDev\Drom\Board\Mapper;

use BaksDev\Drom\Board\Mapper\Elements\DromBoardElementInterface;
use BaksDev\Drom\Board\Mapper\Products\DromBoardProductInterface;
use Exception;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Traversable;

final readonly class DromBoardMapperProvider
{
    public function __construct(
        #[AutowireIterator('baks.drom.board.mapper.products')] private iterable $products,
    ) {}

    /**
     * Возвращает генератор для категорий продуктов, который был теггирован symfony tag
     * @return Traversable<DromBoardProductInterface>
     * @see SweatersAndShirtsProductInterface
     *
     * @see TireProductInterface
     */
    public function getProducts(): iterable
    {
        return $this->products;
    }

    /**
     * Возвращает инстанс класса, который был теггирован symfony tag, фильтрованный по названию элемента
     * @param string $productCategory - название категории от Drom
     */
    public function getProduct(string $productCategory): DromBoardProductInterface
    {
        foreach($this->products as $product)
        {
            if($product->isEqual($productCategory))
            {
                return $product;
            }
        }

        throw new Exception('Не найдена категория продукта с названием '.$productCategory);
    }

    /**
     * Возвращает массив инстансов классов, которые были теггированы symfony tag, фильтрованные по название категории от
     * Drom
     * @param string $productCategory - название категории от Drom
     * @return list<DromBoardElementInterface>
     */
    public function filterElements(string $productCategory): array
    {
        /** @var DromBoardProductInterface $product */
        foreach($this->products as $product)
        {
            if($product->isEqual($productCategory))
            {
                return $product->getElements();
            }
        }

        throw new Exception('Не найдены элементы, относящиеся к категории '.$productCategory);
    }

    /**
     * Возвращает инстанс класса, который был теггирован symfony tag, фильтрованный по название категории от Drom и
     * названию элемента
     * @param string $productCategory - название категории от Drom
     * @param string $elementName - название элемента (прим. AdType)
     */
    public function getElement(string $productCategory, string $elementName): DromBoardElementInterface
    {
        /** @var DromBoardProductInterface $product */
        foreach($this->products as $product)
        {
            if($product->isEqual($productCategory))
            {
                $allElements = $product->getElements();

                foreach($allElements as $element)
                {
                    if($element->element() === $elementName)
                    {
                        return $element;
                    }
                }
            }
        }

        throw new Exception('Не найден элемент с названием: '.$elementName);
    }
}
