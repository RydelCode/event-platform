<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\User;

/**
 * Lightweight test factory for User entities.
 *
 * It only builds the entity; persisting/flushing is left to the caller so the
 * factory stays usable in both unit and integration tests.
 */
final class UserFactory
{
    /**
     * A fixed, non-real hash used purely for tests. Hashing is out of scope on
     * this branch, so we never run the real password hasher here.
     */
    public const string TEST_PASSWORD_HASH = '$2y$04$test.only.hash.not.a.real.credential.0123456789abcd';

    public const string DEFAULT_EMAIL = 'user@example.com';

    /**
     * @param list<string> $roles additional roles only; ROLE_USER is implicit
     */
    public static function create(
        string $email = self::DEFAULT_EMAIL,
        array $roles = [],
        string $password = self::TEST_PASSWORD_HASH,
    ): User {
        $user = new User($email, $password);
        $user->setRoles($roles);

        return $user;
    }
}
