<?php

namespace App\Controller\Api;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    #[Route('/api/events', name: 'api_events_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): JsonResponse
    {
        $events = $eventRepository->findBy([], ['startsAt' => 'ASC']);

        return $this->json(array_map(static function ($event): array {
            return [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'startsAt' => $event->getStartsAt()?->format(DATE_ATOM),
                'endsAt' => $event->getEndsAt()?->format(DATE_ATOM),
                'location' => $event->getLocation(),
                'capacity' => $event->getCapacity(),
            ];
        }, $events));
    }
}
