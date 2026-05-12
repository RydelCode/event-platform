<?php

namespace App\Controller\Api;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Application\Event\CreateEventDto;
use App\Application\Event\CreateEventHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    #[Route('api/events', name: 'api_events_create', methods: ['POST'])]
    public function create(
        Request $request,
        ValidatorInterface $validator,
        CreateEventHandler $handler
    ) : JsonResponse {
        $data = json_decode($request->getContent(), true);

        $dto = CreateEventDto::fromArray($data);

        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            return $this->json([
                'errors' => (string) $errors,
            ], Response::HTTP_BAD_REQUEST);
        }

        $event = $handler->handle($dto);

        return $this->json([
            'message' => 'Event created',
            'id' => $event->getId(),
        ], Response::HTTP_CREATED);
    }
}
