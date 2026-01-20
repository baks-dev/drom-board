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
 *
 */

declare(strict_types=1);

namespace BaksDev\Drom\Board\Mapper\Elements\Tire;

use BaksDev\Drom\Board\Mapper\Elements\DromBoardElementInterface;
use BaksDev\Drom\Board\Mapper\Products\TireProduct;
use BaksDev\Drom\Board\Repository\AllProductsWithMapper\AllProductsWithMapperResult;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 *  Фотографии — вложенные элементы, по одному изображению на каждую позицию в прайс-листе.
 *
 *  Для новых шин система Drom автоматически добавляет изображение, если в прайс-листе оно отсутствует. Для
 *  автоматического добавления необходимо, чтобы у предложения правильно распознались марка и модель. В таком случае
 *  используются изображения из каталога Drom.
 *
 *  Требования и рекомендации к прайс-листам шин
 *  https://baza.drom.ru/help/trebovaniya_k_price_listam_po_shinam#h-11
 */
final readonly class TireImagesElement implements DromBoardElementInterface
{
    private const string ELEMENT = 'picture';

    private const string LABEL = 'Фотография';

    public function __construct(
        #[Autowire(env: 'CDN_HOST')] private string $cdnHost,
        #[Autowire(env: 'HOST')] private string $host,
    ) {}

    public function isMapping(): false
    {
        return false;
    }

    public function isRequired(): false
    {
        return false;
    }

    public function getDefault(): null
    {
        return null;
    }

    public function getHelp(): null
    {
        return null;
    }

    public function fetchData(AllProductsWithMapperResult $data): ?string
    {
        $dromIMG = $this->transform($data->getDromProductImages());

        if(true === empty($dromIMG))
        {
            $dromIMG = $this->transform($data->getProductImages());
        }

        return $dromIMG;
    }

    public function element(): string
    {
        return self::ELEMENT;
    }

    public function label(): string
    {
        return self::LABEL;
    }

    public function getProduct(): string
    {
        return TireProduct::class;
    }

    /** Формируем массив элементов с изображениями */
    private function transform(?array $images): ?string
    {
        if(is_null($images))
        {
            return null;
        }

        $render = null;

        // Сортировка массива элементов с изображениями по root = true
        usort($images, static function($f) {
            return $f->img_root === true ? -1 : 1;
        });

        /**
         * @var object{
         *     img: string,
         *     img_cdn: bool,
         *     img_ext: string,
         *     img_root: bool}|null $image
         */
        foreach($images as $image)
        {
            // Если изображение не загружено - не рендерим
            if(true === empty($image))
            {
                continue;
            }

            $imgHost = 'https://'.($image->img_cdn === true ? $this->cdnHost : $this->host);
            $imgDir = $image->img;
            $imgFile = ($image->img_cdn === true ? '/large.' : '/image.').$image->img_ext;
            $imgPath = $imgHost.$imgDir.$imgFile;
            $element = sprintf('%s', $imgPath, PHP_EOL);
            $render .= $element;
        }

        return $render ?: null;
    }
}
