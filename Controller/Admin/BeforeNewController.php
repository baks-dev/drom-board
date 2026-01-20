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

use BaksDev\Drom\Board\UseCase\BeforeNew\DromBoardCategoryMapperDTO;
use BaksDev\Drom\Board\UseCase\BeforeNew\DromBoardCategoryMapperForm;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_DROM_BOARD_BEFORE_NEW')]
final class BeforeNewController extends AbstractController
{
    /** Создание формы для маппинга локальной категории с категорией Drom для создания формы соотношения свойств */
    #[Route('/admin/drom-board/mapper/before_new', name: 'admin.mapper.beforenew', methods: ['POST', 'GET'])]
    public function beforeNew(Request $request): Response
    {
        $categoryMapperDTO = new DromBoardCategoryMapperDTO();

        $form = $this->createForm(DromBoardCategoryMapperForm::class, $categoryMapperDTO, [
            'action' => $this->generateUrl('drom-board:admin.mapper.beforenew'),
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('mapper_before_new'))
        {
            $this->refreshTokenForm($form);

            return $this->redirectToRoute(
                'drom-board:admin.mapper.new',
                [
                    'localCategory' => $categoryMapperDTO->localCategory,
                    'dromCategory' => $categoryMapperDTO->dromCategory->getProductCategory(),
                ]
            );
        }

        return $this->render(['form' => $form->createView()]);
    }
}
