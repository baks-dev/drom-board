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

namespace BaksDev\Drom\Board\UseCase\Delete\Tests;

use BaksDev\Drom\Board\Entity\DromBoard;
use BaksDev\Drom\Board\Entity\Event\DromBoardEvent;
use BaksDev\Drom\Board\UseCase\Delete\DromBoardDeleteMapperDTO;
use BaksDev\Drom\Board\UseCase\Delete\DromBoardDeleteMapperHandler;
use BaksDev\Drom\Board\UseCase\NewEdit\DromBoardMapperDTO;
use BaksDev\Drom\Board\UseCase\NewEdit\Elements\DromBoardMapperElementDTO;
use BaksDev\Drom\Board\UseCase\NewEdit\Tests\DromBoardMapperEditTest;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Products\Category\UseCase\Admin\Delete\Tests\CategoryProductDeleteTest;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('drom-board')]
#[Group('drom-board-usecase')]
final class DromBoardMapperDeleteTest extends KernelTestCase
{
    #[DependsOnClass(DromBoardMapperEditTest::class)]
    public function testDelete(): void
    {
        $container = self::getContainer();

        /** @var EntityManagerInterface $EntityManager */
        $EntityManager = $container->get(EntityManagerInterface::class);

        /** Тестовый корень */
        $dromBoard = $EntityManager
            ->getRepository(DromBoard::class)
            ->find(CategoryProductUid::TEST);

        self::assertNotNull($dromBoard);

        /** Тестовое активное событие */
        $activeEvent = $EntityManager
            ->getRepository(DromBoardEvent::class)
            ->find($dromBoard->getEvent());

        self::assertNotNull($activeEvent);

        // проверка редактирования
        $editDTO = new DromBoardMapperDTO();

        $activeEvent->getDto($editDTO);

        /** @var ArrayCollection<int, DromBoardMapperElementDTO> $mapperElements */
        $mapperElements = $editDTO->getMapperElements();

        $id = $mapperElements->first();
        $name = $mapperElements->last();

        self::assertEquals('model', $id->getElement());
        self::assertTrue($id->getProductField()->equals(CategoryProductSectionFieldUid::TEST));
        self::assertEquals('DefEdit', $id->getDef());

        self::assertEquals('name', $name->getElement());
        self::assertTrue($name->getProductField()->equals(CategoryProductSectionFieldUid::TEST));
        self::assertEquals('DefEdit', $name->getDef());

        // удаление
        $deleteMapperDTO = new DromBoardDeleteMapperDTO();

        $activeEvent->getDto($deleteMapperDTO);

        /** @var DromBoardDeleteMapperHandler $DromBoardDeleteMapperHandler */
        $DromBoardDeleteMapperHandler = $container->get(DromBoardDeleteMapperHandler::class);
        $deleteDromBoard = $DromBoardDeleteMapperHandler->handle($deleteMapperDTO);
        self::assertTrue($deleteDromBoard instanceof DromBoard);
    }

    #[DependsOnClass(DromBoardMapperEditTest::class)]
    public static function tearDownAfterClass(): void
    {
        /** @var EntityManagerInterface $EntityManager */
        $EntityManager = self::getContainer()->get(EntityManagerInterface::class);

        $events = $EntityManager
            ->getRepository(DromBoardEvent::class)
            ->findBy(['category' => CategoryProductUid::TEST]);

        foreach($events as $event)
        {
            $EntityManager->remove($event);
        }

        $EntityManager->flush();
        $EntityManager->clear();

        /** Удаляем тестовую категорию */
        CategoryProductDeleteTest::tearDownAfterClass();
    }
}
