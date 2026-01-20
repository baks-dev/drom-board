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

use BaksDev\Drom\Board\Repository\AllProductsWithMapper\AllProductsWithMapperResult;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.drom.board.mapper.elements')]
interface DromBoardElementInterface
{
    /**
     * @return true если элемент будет участвовать в маппинге и данные будут браться из формы маппера
     * @return false если элемент не участвует в маппинге и его не нужно показывать в форме маппера
     */
    public function isMapping(): bool;

    /**
     * @return true если значение ОБЯЗАТЕЛЬНО для элемента Drom
     * @return false если значение НЕ ОБЯЗАТЕЛЬНО для элемента Drom
     */
    public function isRequired(): bool;

    /**
     * @return null|false|string - если данные берутся не из класса, а из свойства продукта (БД) по соответствующему
     * ключу
     * @return string - если данные берутся статически, из описания класса
     */
    public function getDefault(): string|false|null;

    /** Возвращает название шаблона формата <@package:path-to-folder> */
    public function getHelp(): ?string;

    /**
     * Извлекает данные из массива данных, форматирует их для тега согласно бизнес логике
     * - @param AllProductsWithMapperResult $data - свойства продукта
     */
    public function fetchData(AllProductsWithMapperResult $data): ?string;

    /** Получает название элемента из константы класса ELEMENT */
    public function element(): string;

    /** Получает описание элемента из константы класса LABEL */
    public function label(): string;

    /**
     * Возвращает название класса реализации @return class-string|null
     * @see DromBoardProductInterface
     */
    public function getProduct(): ?string;
}
