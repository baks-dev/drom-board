<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

declare(strict_types=1);

namespace BaksDev\Drom\Board\UseCase\NewEdit;

use BaksDev\Drom\Board\Entity\Event\DromBoardEventInterface;
use BaksDev\Drom\Board\Type\Event\DromBoardEventUid;
use BaksDev\Drom\Board\UseCase\NewEdit\Elements\DromBoardMapperElementDTO;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Маппим необходимые поля из сущности
 * @see DromBoardEvent
 */
final class DromBoardMapperDTO implements DromBoardEventInterface
{
    #[Assert\Uuid]
    private ?DromBoardEventUid $id = null;

    /** ID локальной категории */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private CategoryProductUid $category;

    /** Идентификатор категории на Drom */
    #[Assert\NotBlank]
    private string $drom;

    /**
     * Коллекция элементов маппера для рендеринга в форме
     * @var ArrayCollection<DromBoardMapperElementDTO> $mapperElements
     */
    #[Assert\Valid]
    private ArrayCollection $mapperElements;


    public function __construct()
    {
        $this->mapperElements = new ArrayCollection();
    }

    public function setId(DromBoardEventUid $id): void
    {
        $this->id = $id;
    }

    public function getEvent(): ?DromBoardEventUid
    {
        return $this->id;
    }

    public function setCategory(CategoryProductUid|CategoryProduct $category): void
    {
        $this->category = $category instanceof CategoryProduct ? $category->getId() : $category;
    }

    public function getCategory(): CategoryProductUid
    {
        return $this->category;
    }

    public function setDrom(string $drom): void
    {
        $this->drom = $drom;
    }

    public function getDrom(): string
    {
        return $this->drom;
    }

    /** @return ArrayCollection<DromBoardMapperElementDTO> */
    public function getMapperElements(): ArrayCollection
    {
        return $this->mapperElements;
    }

    public function addMapperElement(DromBoardMapperElementDTO $element): void
    {
        $this->mapperElements->add($element);
    }

    public function removeMapperElement(DromBoardMapperElementDTO $element): void
    {
        $this->mapperElements->removeElement($element);
    }
}
