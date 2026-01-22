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

namespace BaksDev\Drom\Board\UseCase\NewEdit;

use BaksDev\Drom\Board\Mapper\DromBoardMapperProvider;
use BaksDev\Drom\Board\Mapper\Elements\DromBoardElementInterface;
use BaksDev\Drom\Board\UseCase\NewEdit\Elements\DromBoardMapperElementDTO;
use BaksDev\Drom\Board\UseCase\NewEdit\Elements\DromBoardMapperElementForm;
use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\ModificationCategoryProductSectionField\ModificationCategoryProductSectionFieldInterface;
use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\OffersCategoryProductSectionField\OffersCategoryProductSectionFieldInterface;
use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\PropertyFieldsCategoryChoiceInterface;
use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\VariationCategoryProductSectionField\VariationCategoryProductSectionFieldInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DromBoardMapperForm extends AbstractType
{
    public function __construct(
        private readonly DromBoardMapperProvider $MapperProvider,
        private readonly OffersCategoryProductSectionFieldInterface $OffersCategoryProductSectionField,
        private readonly ModificationCategoryProductSectionFieldInterface $ModificationCategoryProductSectionField,
        private readonly PropertyFieldsCategoryChoiceInterface $PropertyFields,
        private readonly VariationCategoryProductSectionFieldInterface $VariationCategoryProductSectionField,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {

            $form = $event->getForm();

            /** @var DromBoardMapperDTO $dromBoardMapperDTO */
            $dromBoardMapperDTO = $event->getData();

            /** Свойства, ТП, варианты, модификации продукта */
            $productFields = $this->getProductProperties($dromBoardMapperDTO->getCategory());

            /** Реализация продукта Drom */
            $dromProduct = $this->MapperProvider->getProduct($dromBoardMapperDTO->getDrom());

            /**
             * Фильтрация элементов (тегов) по категории - Drom
             * @var list<DromBoardElementInterface>|null $dromElements
             */
            $dromElements = $this->MapperProvider->filterElements($dromBoardMapperDTO->getDrom());

            foreach($dromElements as $element)
            {
                $mapperElementDTO = new DromBoardMapperElementDTO();
                $mapperElementDTO->setElement($element->element());

                $existFormElement = null;

                /** Проверка существования элемента в текущей форме */
                $existFormElement = $dromBoardMapperDTO->getMapperElements()->findFirst(
                    function($key, $element) use ($mapperElementDTO) {
                        /** @var $element DromBoardMapperElementDTO */
                        return $element->getElement() === $mapperElementDTO->getElement();
                    });

                /** При добавлении нового элемента в форму */
                if(true === $element->isMapping() and null === $existFormElement)
                {
                    $dromBoardMapperDTO->addMapperElement($mapperElementDTO);
                }

                /** При удалении уже добавленного элемента в форму */
                if(false === $element->isMapping() and $existFormElement instanceof DromBoardMapperElementDTO)
                {
                    $dromBoardMapperDTO->removeMapperElement($existFormElement);
                }
            }

            $form->add('mapperElements', CollectionType::class, [
                'entry_type' => DromBoardMapperElementForm::class,
                'entry_options' => [
                    'label' => false,
                    'product_fields' => $productFields,
                    'drom_product' => $dromProduct,
                ],
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
            ]);

        });

        $builder->add('mapper_new', SubmitType::class, [
            'label' => 'Save',
            'label_html' => true,
            'attr' => ['class' => 'btn-primary']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DromBoardMapperDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ]);
    }

    /**
     * @return ArrayCollection<CategoryProductSectionFieldUid>
     */
    private function getProductProperties(CategoryProductUid $productCategory): ArrayCollection
    {
        /**
         * Массив с элементами "свойства продукта"
         * @var list<CategoryProductSectionFieldUid> $productProperties
         */
        $productProperties = $this->PropertyFields
            ->category($productCategory)
            ->getPropertyFieldsCollection();

        /** @var ArrayCollection<CategoryProductSectionFieldUid> $productFields */
        $productFields = new ArrayCollection($productProperties);

        /** Торговое предложение */
        $productOffer = $this->OffersCategoryProductSectionField
            ->category($productCategory)
            ->findAllCategoryProductSectionField();

        /** Если нет свойств и ТП - отображаем пустой выпадающий список */
        if($productOffer === false && $productProperties === false)
        {
            $productFields->clear();

            return $productFields;
        }

        if($productOffer)
        {
            $productFields->add($productOffer);

            /** Вариант торгового предложения */
            $productVariation = $this->VariationCategoryProductSectionField
                ->offer((string) $productOffer)
                ->findAllCategoryProductSectionField();

            if($productVariation)
            {
                $productFields->add($productVariation);

                /** Модификация варианта торгового предложения */
                $productModification = $this->ModificationCategoryProductSectionField
                    ->variation((string) $productVariation)
                    ->findAllCategoryProductSectionField();

                if($productModification)
                {
                    $productFields->add($productModification);
                }
            }
        }

        return $productFields;
    }
}