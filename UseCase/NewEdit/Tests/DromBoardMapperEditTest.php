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
use BaksDev\Drom\Board\Type\Event\DromBoardEventUid;
use BaksDev\Drom\Board\UseCase\NewEdit\DromBoardMapperDTO;
use BaksDev\Drom\Board\UseCase\NewEdit\DromBoardMapperHandler;
use BaksDev\Drom\Board\UseCase\NewEdit\Elements\DromBoardMapperElementDTO;
use BaksDev\Core\Type\Modify\Modify\ModifyActionUpdate;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('drom-board')]
#[Group('drom-board-controller')]
#[Group('drom-board-usecase')]
class DromBoardMapperEditTest extends KernelTestCase
{
    #[DependsOnClass(DromBoardMapperNewTest::class)]
    public function testEdit(): void
    {
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $event = $em
            ->getRepository(DromBoardEvent::class)
            ->find(DromBoardEventUid::TEST);

        self::assertNotNull($event);

        $editDTO = new DromBoardMapperDTO();

        $event->getDto($editDTO);

        self::assertTrue($editDTO->getCategory()->equals(CategoryProductUid::TEST));

        /** @var DromBoardMapperElementDTO $mapperElement */
        $mapperElement = $editDTO->getMapperElements()->current();
        self::assertEquals('model', $mapperElement->getElement());
        self::assertEquals('DefNew', $mapperElement->getDef());

        $mapperElement->setDef('DefEdit');

        // добавляем новый маппер в коллекцию
        $mapperElementDTO = new DromBoardMapperElementDTO();
        $mapperElementDTO->setElement('name');
        $mapperElementDTO->setDef('DefEdit');
        $mapperElementDTO->setProductField(new CategoryProductSectionFieldUid());

        $editDTO->addMapperElement($mapperElementDTO);


        /** @var DromBoardMapperHandler $handler */
        $handler = $container->get(DromBoardMapperHandler::class);
        $editDromBoard = $handler->handle($editDTO);
        self::assertTrue($editDromBoard instanceof DromBoard);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $modifier = $em->getRepository(DromBoardModify::class)
            ->find($editDromBoard->getEvent());

        self::assertTrue($modifier->equals(ModifyActionUpdate::ACTION));
    }
}
