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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Drom\Board\BaksDevDromBoardBundle;
use BaksDev\Drom\Board\Type\DromBoardType;
use BaksDev\Drom\Board\Type\DromBoardUid;
use BaksDev\Drom\Board\Type\Event\DromBoardEventType;
use BaksDev\Drom\Board\Type\Event\DromBoardEventUid;
use Symfony\Config\DoctrineConfig;

return static function(DoctrineConfig $doctrine): void {

    $doctrine->dbal()->type(DromBoardUid::TYPE)->class(DromBoardType::class);
    $doctrine->dbal()->type(DromBoardEventUid::TYPE)->class(DromBoardEventType::class);

    $emDefault = $doctrine->orm()->entityManager('default')->autoMapping(true);

    $emDefault->mapping('drom-board')
        ->type('attribute')
        ->dir(BaksDevDromBoardBundle::PATH.'Entity')
        ->isBundle(false)
        ->prefix(BaksDevDromBoardBundle::NAMESPACE.'\\Entity')
        ->alias('drom-board');
};
