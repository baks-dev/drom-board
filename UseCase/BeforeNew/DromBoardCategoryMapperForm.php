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

namespace BaksDev\Drom\Board\UseCase\BeforeNew;

use BaksDev\Drom\Board\Mapper\DromBoardMapperProvider;
use BaksDev\Drom\Board\Mapper\Products\DromBoardProductInterface;
use BaksDev\Drom\Board\Repository\AllCategoryWithMapper\AllCategoryWithDromMapperRepository;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DromBoardCategoryMapperForm extends AbstractType
{
    public function __construct(
        private readonly DromBoardMapperProvider $MapperProvider,
        private readonly AllCategoryWithDromMapperRepository $AllCategoryWithDromMapperRepository,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** Получаем список локальных категории */
        $localCategories = $this->AllCategoryWithDromMapperRepository->findAll();

        /** Получаем список наших категорий товаров */
        $builder
            ->add('localCategory', ChoiceType::class, [
                'choices' => $localCategories,
                'choice_value' => function(?CategoryProductUid $type) {
                    return $type?->getValue();
                },
                'choice_label' => function(CategoryProductUid $type) {
                    return $type->getOptions();
                },
                'label' => false,
                'expanded' => false,
                'multiple' => false,
                'required' => true,
            ]);

        /** Получаем список категории продуктов Drom */
        $dromCategories = $this->MapperProvider->getProducts();

        $builder
            ->add('dromCategory', ChoiceType::class, [
                'choices' => $dromCategories,
                'choice_value' => static function(?DromBoardProductInterface $dromCategories) {
                    return $dromCategories?->getProductCategory();
                },
                'choice_label' => static function(DromBoardProductInterface $dromCategories) {
                    return $dromCategories->getProductCategory();
                },
                'expanded' => false,
                'multiple' => false,
            ]);

        $builder->add(
            'mapper_before_new',
            SubmitType::class,
            ['label_html' => true, 'attr' => ['class' => 'btn-primary']],
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => DromBoardCategoryMapperDTO::class,
                'method' => 'POST',
                'attr' => ['class' => 'w-100'],
            ],
        );
    }
}
