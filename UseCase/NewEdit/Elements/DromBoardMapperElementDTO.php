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

namespace BaksDev\Drom\Board\UseCase\NewEdit\Elements;

use BaksDev\Drom\Board\Entity\Element\DromBoardMapperElementInterface;
use BaksDev\Drom\Board\Mapper\Elements\DromBoardElementInterface;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use Symfony\Component\Validator\Constraints as Assert;

/** @see DromBoardMapperElement */
final class DromBoardMapperElementDTO implements DromBoardMapperElementInterface
{
    #[Assert\NotBlank]
    private ?string $element = null;

    /** Связь на свойство продукта в категории */
    #[Assert\Uuid]
    #[Assert\When(expression: 'this.getDef() === null', constraints: new Assert\NotBlank())]
    private ?CategoryProductSectionFieldUid $productField = null;

    /** Значение по умолчанию */
    #[Assert\When(expression: 'this.getProductField() === null', constraints: new Assert\NotBlank())]
    private ?string $def = null;

    /** Для доступа к методам объекта в форме @see DromBoardMapperElementForm */
    private ?DromBoardElementInterface $elementInstance = null;

    public function getElementInstance(): ?DromBoardElementInterface
    {
        return $this->elementInstance;
    }

    public function setElementInstance(?DromBoardElementInterface $elementInstance): void
    {
        $this->elementInstance = $elementInstance;
    }

    public function getProductField(): ?CategoryProductSectionFieldUid
    {
        return $this->productField;
    }

    public function setProductField(?CategoryProductSectionFieldUid $productField): void
    {
        $this->productField = $productField;
    }

    public function getDef(): ?string
    {
        return $this->def;
    }

    public function setDef(?string $default): self
    {
        $this->def = $default;
        return $this;
    }

    public function getElement(): ?string
    {
        return $this->element;
    }

    public function setElement(?string $element): void
    {
        $this->element = $element;
    }
}