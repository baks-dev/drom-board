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

namespace BaksDev\Drom\Board\Twig;

use BaksDev\Drom\Board\Mapper\DromBoardMapperProvider;
use BaksDev\Drom\Board\Mapper\Elements\DromBoardElementInterface;
use BaksDev\Drom\Board\Repository\AllProductsWithMapper\AllProductsWithMapperResult;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ProductTransformerExtension extends AbstractExtension
{
    private AllProductsWithMapperResult $productResult;

    private ?string $dromCategory = null;

    private ?array $dromBoardPropertyMapper = null;

    private ?string $productName = null;

    private ?string $productArticle = null;

    public function __construct(
        #[Target('dromBoardLogger')] private readonly LoggerInterface $logger,
        private readonly DromBoardMapperProvider $mapperProvider,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('drom_product_transform', [$this, 'productTransform']),
        ];
    }

    public function productTransform(AllProductsWithMapperResult $product): ?array
    {
        $this->productResult = $product;
        $this->productName = $product->getProductName();
        $this->productArticle = $product->getProductArticle();

        $this->dromCategory = $product->getDromBoardDromCategory();
        $this->dromBoardPropertyMapper = $product->getDromBoardPropertyMapper();

        /** Список всех элементов категории */
        $dromBoardElements = $this->mapperProvider->filterElements($this->dromCategory);

        /** Получаем элементы по категории продукта, НЕ УЧАСТВУЮЩИЕ в маппинге */
        $unmappedElements = array_filter(
            $dromBoardElements,
            static function(DromBoardElementInterface $element) {
                return $element->isMapping() === false;
            },
        );

        /**
         * Формируем массив для отрисовки в фиде, где ключ - название элемента, значение - значением из свойств продукта
         */
        $elements = null;

        foreach($unmappedElements as $element)
        {
            $data = $element->fetchData($product);

            /** Если у продукта есть свойство null, обязательное для Drom - пропускаем продукт, пишем в лог */
            if($data === null && $element->isRequired())
            {
                $this->logger->critical(
                    sprintf(
                        'В свойства продукта не найдено значение для обязательного элемента Drom! Название элемента: %s. Название продукта: %s. Артикул продукта: %s',
                        $element->element(),
                        $this->productName,
                        $this->productArticle,
                    ),
                    [self::class.':'.__LINE__],
                );

                return null;
            }


            /** Добавляем элемент если имеется результат значения */
            if(null !== $data)
            {
                $elements[$element->element()] = $data;
            }
        }

        /** Преобразуем строку маппера в массив элементов */
        $mappedElements = $this->getElements();

        if(empty($mappedElements))
        {
            return null;
        }

        return array_merge($mappedElements, $elements);
    }

    private function getElements(): ?array
    {
        $mapper = $this->dromBoardPropertyMapper;

        if(true === is_null($mapper))
        {
            $this->logger->critical(
                sprintf(
                    'Соотношение свойств не найдено! Название продукта: %s. Артикул продукта: %s',
                    $this->productName,
                    $this->productArticle,
                ),
                [self::class.':'.__LINE__],
            );

            return null;
        }

        $require = false;

        /**
         * Ищем для элементов маппера кастомные связанные элементы и преобразуем согласно формату из элемента методом
         * fetchData
         */
        array_walk($mapper, function(&$value, $element) use (&$require) {

            $DromBoardElementInstance = $this->mapperProvider->getElement($this->dromCategory, $element);

            $value = $DromBoardElementInstance->fetchData($this->productResult);

            /** Если у продукта есть свойство null, обязательное для Drom - пропускаем продукт, пишем в лог */
            if(null === $value && $DromBoardElementInstance->isRequired())
            {
                $require = true;

                $this->logger->warning(
                    sprintf(
                        '
                        В свойства продукта не найдено значение для обязательного элемента Drom! 
                        Название элемента: %s. Название продукта: %s. Артикул продукта: %s',
                        $element,
                        $this->productName,
                        $this->productArticle,
                    ),
                    [self::class.':'.__LINE__],
                );
            }
        });

        if($require === true)
        {
            return null;
        }

        return $mapper;
    }
}