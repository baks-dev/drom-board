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

namespace BaksDev\Drom\Board\Mapper\Tests;

use BaksDev\Drom\Board\Repository\AllProductsWithMapper\AllProductsWithMapperInterface;
use BaksDev\Drom\Board\Twig\ProductTransformerExtension;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Generator;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('drom-board')]
#[Group('drom-board-mapper')]
class DromMapperTest extends KernelTestCase
{
    private static array|null $feed = null;

    private static Generator|false $products = false;

    public static function setUpBeforeClass(): void
    {
        /** @var AllProductsWithMapperInterface $AllProductsWithMapper */
        $AllProductsWithMapper = self::getContainer()->get(AllProductsWithMapperInterface::class);

        $profileUid = $_SERVER['TEST_PROFILE'] ?? UserProfileUid::TEST;

        $products = $AllProductsWithMapper
            ->forProfile(new UserProfileUid($profileUid))
            ->findAll();

        if(false === $products)
        {
            self::assertTrue(true);
            return;
        }

        self::$products = $products;
    }

    public function testMapping(): void
    {
        if(false === self::$products)
        {
            self::assertTrue(true);
            return;
        }

        $products = self::$products;

        /** @var ProductTransformerExtension $ProductTransformerExtension */
        $ProductTransformerExtension = self::getContainer()->get(ProductTransformerExtension::class);

        // время маппинга
        $start = hrtime(true);
        foreach($products as $product)
        {
            $mappingProduct = $ProductTransformerExtension->productTransform($product);

            if(false === is_null($mappingProduct))
            {
                self::$feed[] = $mappingProduct;
            }
        }
        $end = hrtime(true);

        /** Если теги не смаппились и фид пустой */
        if(true === is_null(self::$feed))
        {
            self::assertTrue(true);
            return;
        }

        self::assertIsArray(self::$feed);

        //  $durationMs = ($end - $start) / 1e+6;
        //  $durationSec = $durationMs / 1000;     // секунды
        //  dump('====================');
        //  dump('⏱ Общее время маппинга - '.number_format($durationSec, 2).' sec');
        //  dump('количество продуктов, добавленных в фид - '.count(self::$feed));
    }
}