<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testNewUserAlwaysHasRoleUser(): void
    {
        $user = UserFactory::create(roles: []);

        self::assertSame(['ROLE_USER'], $user->getRoles());
    }

    public function testAdditionalRolesAreReturnedAlongsideRoleUser(): void
    {
        $user = UserFactory::create(roles: ['ROLE_ORGANIZER']);

        self::assertContains('ROLE_ORGANIZER', $user->getRoles());
        self::assertContains('ROLE_USER', $user->getRoles());
    }

    public function testRolesAreNotDuplicatedWhenRoleUserIsStoredExplicitly(): void
    {
        $user = UserFactory::create(roles: ['ROLE_USER', 'ROLE_ADMIN']);

        $roles = $user->getRoles();

        // Order-independent assertions to keep the test robust.
        sort($roles);
        self::assertSame(['ROLE_ADMIN', 'ROLE_USER'], $roles);
    }

    public function testUserIdentifierIsTheEmail(): void
    {
        $user = UserFactory::create(email: 'organizer@example.com');

        self::assertSame('organizer@example.com', $user->getUserIdentifier());
    }

    public function testEmailIsNormalizedOnConstruction(): void
    {
        $user = new User('  Foo@Example.com  ', UserFactory::TEST_PASSWORD_HASH);

        self::assertSame('foo@example.com', $user->getEmail());
        self::assertSame('foo@example.com', $user->getUserIdentifier());
    }
}
