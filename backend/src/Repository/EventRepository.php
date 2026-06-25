<?php

declare(strict_types=1);

namespace App\Repository;

use App\Application\Event\ListEvents\EventListItemDto;
use App\Domain\Event\EventStatus;
use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findWithPessimisticWriteLock(int $eventId): ?Event
    {
        return $this->find($eventId, LockMode::PESSIMISTIC_WRITE);
    }

    public function getById(int $id): ?Event
    {
        return $this->find($id);
    }

    public function findList(
        int $page,
        int $limit,
        ?EventStatus $status,
        ?\DateTimeImmutable $startsAfter,
        string $sort,
        string $order,
    ): array {
        $qb = $this->createQueryBuilder('e')
            ->select(sprintf('NEW %s(e.id, e.title, e.description, e.location, e.capacity)', EventListItemDto::class))
            ->orderBy('e.'.$sort, $order)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $this->applyFilters($qb, $status, $startsAfter);

        return $qb->getQuery()->getArrayResult();
    }

    public function countList(
        ?EventStatus $status,
        ?\DateTimeImmutable $startsAfter,
    ): int {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)');

        $this->applyFilters($qb, $status, $startsAfter);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyFilters(
        QueryBuilder $qb,
        ?EventStatus $status,
        ?\DateTimeImmutable $startsAfter,
    ): void {
        if (null !== $status) {
            $qb->andWhere('e.status = :status')
                ->setParameter('status', $status);
        }

        if (null !== $startsAfter) {
            $qb->andWhere('e.startsAt > :startsAfter')
                ->setParameter('startsAfter', $startsAfter);
        }
    }
}
