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

namespace BaksDev\Drom\Board\Repository\AllCategoryWithMapper;

use BaksDev\Drom\Board\Entity\DromBoard;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Event\CategoryProductEvent;
use BaksDev\Products\Category\Entity\Info\CategoryProductInfo;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Generator;

final class AllCategoryWithDromMapperRepository implements AllCategoryWithDromMapperInterface
{
    private bool $active = false;

    private ?CategoryProductUid $category = null;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    /** Соответствие по активной категории */
    public function onlyActive(): self
    {
        $this->active = true;

        return $this;
    }

    /** Соответствие по категории */
    public function forCategory(CategoryProduct|CategoryProductUid $category): self
    {
        if($category instanceof CategoryProduct)
        {
            $category = $category->getId();
        }

        $this->category = $category;

        return $this;
    }

    /** Получаем коллекцию категорий, для которых не найдено совпадений с категорией из маппера */
    public function findAll(): Generator
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        /** Категория */
        $dbal
            ->from(CategoryProduct::class, 'category');

        $dbal
            ->joinRecursive(
                'category',
                CategoryProductEvent::class,
                'category_event',
                'category_event.id = category.event',
            );

        $dbal
            ->leftJoin(
                'category',
                CategoryProductTrans::class,
                'category_trans',
                'category_trans.event = category.event AND category_trans.local = :local',
            );

        /** Категория с определенным идентификатором */
        if($this->category)
        {
            $dbal
                ->where('category.id = :category')
                ->setParameter('category', $this->category, CategoryProductUid::TYPE);
        }

        /** Выбираем только активные категории */
        if($this->active)
        {
            $dbal->join(
                'category',
                CategoryProductInfo::class,
                'info',
                '
                info.event = category.event AND 
                info.active = true',
            );
        }

        $dbal
            ->select('category.id')
            ->addSelect('category_event.sort')
            ->addSelect('category_event.parent')
            ->addSelect('category_trans.name');

        /** Совпадение с категорией из маппера */
        $dbal->leftJoin(
            'category',
            DromBoard::class,
            'drom_board',
            'category.id = drom_board.id',
        );

        /** Категории, для которых совпадений с категорией из маппера не найдено */
        $dbal->andWhere('drom_board.id IS NULL');

        $result = $dbal->findAllRecursive(['parent' => 'id']);

        foreach($result as $item)
        {
            yield new CategoryProductUid($item['id'], $item['name'], $item['parent']);
        }
    }
}
