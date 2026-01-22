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

namespace BaksDev\Drom\Board\UseCase\NewEdit\Tests;

use BaksDev\Drom\Board\Entity\DromBoard;
use BaksDev\Drom\Board\Entity\Event\DromBoardEvent;
use BaksDev\Drom\Board\Entity\Modify\DromBoardModify;
use BaksDev\Drom\Board\UseCase\NewEdit\DromBoardMapperDTO;
use BaksDev\Drom\Board\UseCase\NewEdit\DromBoardMapperHandler;
use BaksDev\Drom\Board\UseCase\NewEdit\Elements\DromBoardMapperElementDTO;
use BaksDev\Core\Type\Modify\Modify\ModifyActionNew;
use BaksDev\Drom\Products\UseCase\NewEdit\Tests\DromProductNewTest;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('drom-board')]
#[Group('drom-board-controller')]
#[Group('drom-board-usecase')]
#[Group('drom-board-repository')]
class DromBoardMapperNewTest extends KernelTestCase
{
    public static function setUpBeforeClass(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $DromBoard = $em->getRepository(DromBoard::class)
            ->find(CategoryProductUid::TEST);

        if($DromBoard)
        {
            $em->remove($DromBoard);
        }

        $DromBoardEvent = $em->getRepository(DromBoardEvent::class)
            ->findBy(['category' => CategoryProductUid::TEST]);

        foreach($DromBoardEvent as $event)
        {
            $em->remove($event);
        }

        $em->flush();
        $em->clear();


        /** Создаем тестовый продукт Drom */
        DromProductNewTest::setUpBeforeClass();
        new DromProductNewTest('')->testNew();
    }

    public function testNew(): void
    {
        $newDTO = new DromBoardMapperDTO();

        // добавляем категории
        $newDTO->setCategory(new CategoryProductUid(CategoryProductUid::TEST));
        self::assertTrue($newDTO->getCategory()->equals(CategoryProductUid::TEST));

        $newDTO->setDrom('Шины');
        self::assertSame('Шины', $newDTO->getDrom());

        // добавляем элементы для маппинга
        $mapperElementDTO = new DromBoardMapperElementDTO();

        $mapperElementDTO->setElement('model');
        self::assertSame('model', $mapperElementDTO->getElement());

        $mapperElementDTO->setProductField(new CategoryProductSectionFieldUid());
        self::assertTrue($mapperElementDTO->getProductField()->equals(CategoryProductSectionFieldUid::TEST));

        $mapperElementDTO->setDef('DefNew');
        self::assertSame('DefNew', $mapperElementDTO->getDef());

        $newDTO->addMapperElement($mapperElementDTO);

        $container = self::getContainer();

        /** @var DromBoardMapperHandler $handler */
        $handler = $container->get(DromBoardMapperHandler::class);
        $newDromBoard = $handler->handle($newDTO);
        self::assertTrue($newDromBoard instanceof DromBoard);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $modifier = $em->getRepository(DromBoardModify::class)
            ->find($newDromBoard->getEvent());

        self::assertTrue($modifier->equals(ModifyActionNew::ACTION));
    }
}
