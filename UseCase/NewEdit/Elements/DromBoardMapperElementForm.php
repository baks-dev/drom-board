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

namespace BaksDev\Drom\Board\UseCase\NewEdit\Elements;

use BaksDev\Drom\Board\Mapper\Products\DromBoardProductInterface;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** Форма для отрисовки полей соответствия между категориями */
final class DromBoardMapperElementForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($options) {

            $form = $event->getForm();

            /** @var DromBoardMapperElementDTO $mapperElementDTO */
            if($mapperElementDTO = $event->getData())
            {
                /** @var DromBoardProductInterface $dromProduct */
                $dromProduct = $options['drom_product'];
                $element = $dromProduct->getElement($mapperElementDTO->getElement());

                /** Для доступа к методам объекта в форме @see DromBoardMapperElementDTO */
                $mapperElementDTO->setElementInstance($element);

                /** @var ArrayCollection<CategoryProductSectionFieldUid> $productFields */
                $productFields = $options['product_fields'];

                $form
                    ->add('productField', ChoiceType::class, [
                        'choices' => $productFields,
                        'choice_value' => function(?CategoryProductSectionFieldUid $field) {
                            return $field?->getValue();
                        },
                        'choice_label' => function(CategoryProductSectionFieldUid $field) {
                            return $field->getAttr();
                        },
                        'label' => $element->label(),
                        'help' => $element->getHelp(),
                        'expanded' => false,
                        'multiple' => false,
                        'required' => false,
                    ]);

                $form->add('def', HiddenType::class, [
                    'data' => $mapperElementDTO->getDef() ?? $element->getDefault(),
                    'label' => $element->label(),
                    'help' => $element->getHelp(),
                    'translation_domain' => 'drom-board.settings',
                    'required' => false,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => DromBoardMapperElementDTO::class,
                'product_fields' => null,
                'drom_product' => null,
            ]
        );
    }
}