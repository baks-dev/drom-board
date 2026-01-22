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

namespace BaksDev\Drom\Board\Entity\Event;

use BaksDev\Drom\Board\Entity\DromBoard;
use BaksDev\Drom\Board\Entity\Element\DromBoardMapperElement;
use BaksDev\Drom\Board\Entity\Modify\DromBoardModify;
use BaksDev\Drom\Board\Type\Event\DromBoardEventUid;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/** @see DromBoardMapperDTO */
#[ORM\Entity]
#[ORM\Table(name: 'drom_board_event')]
class DromBoardEvent extends EntityEvent
{
    /** Идентификатор События - Uuid Value Object */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: DromBoardEventUid::TYPE)]
    private DromBoardEventUid $id;

    /** Идентификатор локальной категории */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: CategoryProductUid::TYPE)]
    private CategoryProductUid $category;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING)]
    private string $drom;

    /** Модификатор */
    #[ORM\OneToOne(targetEntity: DromBoardModify::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private DromBoardModify $modify;

    /** Связь с характеристиками продукта от Дром */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: DromBoardMapperElement::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $mapperElements;

    public function __construct()
    {
        $this->id = new DromBoardEventUid();
        $this->modify = new DromBoardModify($this);
    }

    /** Используется при модификации - update */
    public function __clone(): void
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    /** Магический метод гидрирования DTO с помощью рефлексии */
    public function getDto($dto): mixed
    {
        if($dto instanceof DromBoardEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if($dto instanceof DromBoardEventInterface)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function getId(): DromBoardEventUid
    {
        return $this->id;
    }

    /** Сеттер для корневой сущности, к которой относится данное событие */
    public function setMain(DromBoard|CategoryProductUid $main): void
    {
        $this->category = $main instanceof DromBoard ? $main->getId() : $main;
    }

    public function getCategory(): CategoryProductUid
    {
        return $this->category;
    }

    public function getDrom(): string
    {
        return $this->drom;
    }
}
