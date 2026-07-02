<?php

declare(strict_types=1);

namespace App\Repository;

use App\Application\Event\ListEvents\EventListItemDto;
use App\Application\Event\ListEvents\EventSortField;
use App\Application\Event\ListEvents\SortDirection;
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

    /**
     * @return list<EventListItemDto>
     */
    public function findList(
        int $page,
        int $limit,
        ?EventStatus $status,
        ?\DateTimeImmutable $startsAfter,
        EventSortField $sort,
        SortDirection $direction,
    ): array {
        $sortField = match ($sort) {
            EventSortField::STARTS_AT => 'e.startsAt',
            EventSortField::ENDS_AT => 'e.endsAt',
        };

        $qb = $this->createQueryBuilder('e')
            ->select(sprintf('NEW %s(e.id, e.status, e.title, e.description, e.startsAt, e.endsAt, e.location, e.capacity)', EventListItemDto::class))
            ->orderBy($sortField, $direction->value)
            ->addOrderBy('e.id', $direction->value)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $this->applyFilters($qb, $status, $startsAfter);

        return $qb->getQuery()->getResult();
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
