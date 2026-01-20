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

namespace BaksDev\Drom\Board\Repository\AllProductsWithMapper\Tests;

use BaksDev\Drom\Board\Repository\AllProductsWithMapper\AllProductsWithMapperInterface;
use BaksDev\Drom\Board\Repository\AllProductsWithMapper\AllProductsWithMapperResult;
use BaksDev\Drom\Products\UseCase\NewEdit\Images\Tests\DromProductImagesNewTest;
use BaksDev\Drom\UseCase\Admin\NewEdit\Tests\DromTokenNewTest;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('drom-board')]
#[Group('drom-board-repository')]
class AllProductsWithMapperRepositoryTest extends KernelTestCase
{
    #[DependsOnClass(DromTokenNewTest::class)]
    #[DependsOnClass(DromProductImagesNewTest::class)]
    public function testRepository(): void
    {
        /** @var AllProductsWithMapperInterface $AllProductsWithMapper */
        $AllProductsWithMapper = self::getContainer()->get(AllProductsWithMapperInterface::class);

        $profileUid = $_SERVER['TEST_DROM_PROFILE'] ?? UserProfileUid::TEST;

        $products = $AllProductsWithMapper
            ->forProfile(new UserProfileUid(''))
            ->forProduct(new ProductUid(''))
            ->forOfferConst(new ProductOfferConst(''))
            ->forVariationConst(new ProductVariationConst(''))
            ->forModificationConst(new ProductModificationConst(''))
            ->findAll();

        if(false !== $products)
        {
            foreach($products as $AllProductsWithMapperResult)
            {
                // Вызываем все геттеры
                $reflectionClass = new ReflectionClass(AllProductsWithMapperResult::class);
                $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

                foreach($methods as $method)
                {
                    // Методы без аргументов
                    if($method->getNumberOfParameters() === 0)
                    {
                        // Вызываем метод
                        $data = $method->invoke($AllProductsWithMapperResult);
//                        dump($data);
                    }
                }
            }
        }

        self::assertTrue(true);
    }
}
