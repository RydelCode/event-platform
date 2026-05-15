<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Application\Event\CreateEvent\CreateEventDto;
use App\Application\Event\CreateEvent\CreateEventHandler;
use App\Application\Event\GetEventDetails\GetEventDetailsHandler;
use App\Application\Event\ListEvents\ListEventsHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class EventController extends AbstractController
{
    #[Route('/api/events', name: 'api_events_index', methods: ['GET'])]
    public function index(ListEventsHandler $handler): JsonResponse
    {
        return $this->json($handler->handle());
    }

    #[Route('/api/events', name: 'api_events_create', methods: ['POST'])]
    public function create(
        Request $request,
        ValidatorInterface $validator,
        CreateEventHandler $handler
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $dto = CreateEventDto::fromArray(is_array($data) ? $data : []);

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

    #[Route('/api/events/{id}', name: 'api_events_details', methods: ['GET'])]
    public function details(
        int $id,
        GetEventDetailsHandler $handler
    ): JsonResponse {
        $event = $handler->handle($id);

        if (is_null($event)) {
            return $this->json([
            'message' => 'No event found with ID: ' . $id,
            ],
            Response::HTTP_NOT_FOUND);
        }

        return $this->json($event);
    }
}
