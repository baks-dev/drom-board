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

namespace BaksDev\Drom\Board\Controller\Admin\Tests;

use BaksDev\Drom\Board\Entity\DromBoard;
use BaksDev\Drom\Board\Entity\Event\DromBoardEvent;
use BaksDev\Drom\Board\UseCase\NewEdit\Tests\DromBoardMapperEditTest;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Users\User\Tests\TestUserAccount;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('drom-board')]
#[Group('drom-board-controller')]
final class EditAdminControllerTest extends WebTestCase
{
    private const string ROLE = 'ROLE_DROM_BOARD_EDIT';

    private static string $url = '/admin/drom-board/mapper/edit/%s';

    #[DependsOnClass(DromBoardMapperEditTest::class)]
    public static function setUpBeforeClass(): void
    {
        /** @var EntityManagerInterface $EntityManager */
        $EntityManager = self::getContainer()->get(EntityManagerInterface::class);

        /** Находим корень */
        $dromBoard = $EntityManager
            ->getRepository(DromBoard::class)
            ->find(CategoryProductUid::TEST);

        if(empty($dromBoard))
        {
            self::assertNull($dromBoard);
            return;
        }

        self::assertNotNull($dromBoard);

        /** Находим активное событие */
        $activeEvent = $EntityManager
            ->getRepository(DromBoardEvent::class)
            ->find($dromBoard->getEvent());

        self::assertNotNull($activeEvent);

        self::$url = sprintf(self::$url, $activeEvent);

        $EntityManager->clear();
    }

    /** Доступ по роли */
    #[DependsOnClass(DromBoardMapperEditTest::class)]
    public function testRoleSuccessful(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);

            $usr = TestUserAccount::getModer(self::ROLE);

            $client->loginUser($usr, 'user');
            $client->request('GET', self::$url);

            self::assertResponseIsSuccessful();
        }
    }

    /** Доступ по роли ROLE_ADMIN */
    #[DependsOnClass(DromBoardMapperEditTest::class)]
    public function testRoleAdminSuccessful(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);
            $usr = TestUserAccount::getAdmin();

            $client->loginUser($usr, 'user');
            $client->request('GET', self::$url);

            self::assertResponseIsSuccessful();
        }
    }

    /** Доступ по роли ROLE_USER */
    public function testRoleUserDeny(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);

            $usr = TestUserAccount::getUsr();
            $client->loginUser($usr, 'user');
            $client->request('GET', self::$url);

            self::assertResponseStatusCodeSame(403);
        }
    }

    /** Доступ без роли */
    #[DependsOnClass(DromBoardMapperEditTest::class)]
    public function testGuestFiled(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();

        foreach(TestUserAccount::getDevice() as $device)
        {
            $client->setServerParameter('HTTP_USER_AGENT', $device);
            $client->request('GET', self::$url);

            // Full authentication is required to access this resource
            self::assertResponseStatusCodeSame(401);
        }
    }
}
