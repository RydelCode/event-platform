<?php

declare(strict_types=1);

namespace App\Tests\Integration\Entity;

use App\Entity\User;
use App\Tests\Factory\UserFactory;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UserPersistenceTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);

        $this->entityManager = $entityManager;
    }

    public function testUserIsPersistedAndReloadedWithItsValues(): void
    {
        $user = UserFactory::create(
            email: 'stored@example.com',
            roles: ['ROLE_ORGANIZER'],
        );
        $createdAtBeforeSave = $user->getCreatedAt();

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $id = $user->getId();

        $this->entityManager->clear();

        $reloaded = $this->entityManager->find(User::class, $id);

        self::assertInstanceOf(User::class, $reloaded);
        self::assertSame('stored@example.com', $reloaded->getEmail());
        self::assertSame(UserFactory::TEST_PASSWORD_HASH, $reloaded->getPassword());
        self::assertContains('ROLE_ORGANIZER', $reloaded->getRoles());
        self::assertContains('ROLE_USER', $reloaded->getRoles());

        // The stored column is TIMESTAMP(0); compare at second precision so the
        // test verifies the persisted value, not the exact current time.
        self::assertSame(
            $createdAtBeforeSave->format('Y-m-d H:i:s'),
            $reloaded->getCreatedAt()->format('Y-m-d H:i:s'),
        );
    }

    public function testDuplicateEmailViolatesDatabaseUniqueConstraint(): void
    {
        $this->entityManager->persist(UserFactory::create(email: 'duplicate@example.com'));
        $this->entityManager->flush();

        $this->entityManager->persist(UserFactory::create(email: 'duplicate@example.com'));

        $this->expectException(UniqueConstraintViolationException::class);

        $this->entityManager->flush();
    }

    public function testEmailUniquenessIsCaseInsensitiveThroughNormalization(): void
    {
        $this->entityManager->persist(UserFactory::create(email: 'Foo@Example.com'));
        $this->entityManager->flush();

        // Normalized to the same 'foo@example.com', so the DB constraint fires.
        $this->entityManager->persist(UserFactory::create(email: 'foo@example.com'));

        $this->expectException(UniqueConstraintViolationException::class);

        $this->entityManager->flush();
    }
}
