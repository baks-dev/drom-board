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

namespace BaksDev\Drom\Board\Controller\Admin;

use BaksDev\Drom\Board\Entity\DromBoard;
use BaksDev\Drom\Board\UseCase\NewEdit\DromBoardMapperDTO;
use BaksDev\Drom\Board\UseCase\NewEdit\DromBoardMapperForm;
use BaksDev\Drom\Board\UseCase\NewEdit\DromBoardMapperHandler;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Products\Category\Entity\CategoryProduct;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_DROM_BOARD_NEW')]
final class NewController extends AbstractController
{
    /** Создание формы сопоставления элементов категорий */
    #[Route(
        '/admin/drom-board/mapper/new/{localCategory}/{dromCategory}',
        name: 'admin.mapper.new',
        requirements: ['localCategory' => '^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$'],
        methods: ['GET', 'POST']
    )]
    public function new(
        Request $request,
        DromBoardMapperHandler $DromBoardMapperHandler,
        #[MapEntity] CategoryProduct $localCategory,
        string $dromCategory
    ): Response
    {
        $mapperDTO = new DromBoardMapperDTO();
        $mapperDTO->setCategory($localCategory);
        $mapperDTO->setDrom($dromCategory);

        $form = $this->createForm(DromBoardMapperForm::class, $mapperDTO);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('mapper_new'))
        {
            $this->refreshTokenForm($form);

            $result = $DromBoardMapperHandler->handle($mapperDTO);

            if($result instanceof DromBoard)
            {
                $this->addFlash('page.new', 'success.new', 'drom-board.admin');

                return $this->redirectToRoute('drom-board:admin.mapper.index');
            }

            $this->addFlash('page.new', 'danger.new', 'drom-board.admin', $result);

            return $this->redirectToReferer();
        }

        return $this->render(['form' => $form->createView()]);
    }
}
