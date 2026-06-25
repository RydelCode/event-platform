<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Application\Event\CancelEvent\CancelEventHandler;
use App\Application\Event\CompleteEvent\CompleteEventHandler;
use App\Application\Event\CreateEvent\CreateEventDto;
use App\Application\Event\CreateEvent\CreateEventHandler;
use App\Application\Event\EventTransitionHandler;
use App\Application\Event\GetEventDetails\GetEventDetailsHandler;
use App\Application\Event\ListEvents\EventListQueryDto;
use App\Application\Event\ListEvents\ListEventsHandler;
use App\Application\Event\PublishEvent\PublishEventHandler;
use App\Application\Event\RegisterForEvent\DuplicateRegistrationDetectedException;
use App\Application\Event\RegisterForEvent\EventCapacityExceededException;
use App\Application\Event\RegisterForEvent\EventNotPublishedException;
use App\Application\Event\RegisterForEvent\RegisterForEventDto;
use App\Application\Event\RegisterForEvent\RegisterForEventHandler;
use App\Domain\Event\EventCannotBeCancelledException;
use App\Domain\Event\EventCannotBeCompletedException;
use App\Domain\Event\EventCannotBePublishedException;
use App\Infrastructure\Http\ValidationErrorFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class EventController extends AbstractController
{
    #[Route('/api/events', name: 'api_events_index', methods: ['GET'])]
    public function index(
        #[MapQueryString] EventListQueryDto $query,
        ListEventsHandler $handler): JsonResponse
    {
        return $this->json($handler->handle($query));
    }

    #[Route('/api/events', name: 'api_events_create', methods: ['POST'])]
    public function create(
        Request $request,
        ValidatorInterface $validator,
        ValidationErrorFormatter $validationErrorFormatter,
        CreateEventHandler $handler,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $dto = CreateEventDto::fromArray(is_array($data) ? $data : []);

        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            return $this->json([
                'errors' => $validationErrorFormatter->format($errors),
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
        GetEventDetailsHandler $handler,
    ): JsonResponse {
        $event = $handler->handle($id);

        if (is_null($event)) {
            return $this->json([
                'message' => 'No event found with ID: '.$id,
            ],
                Response::HTTP_NOT_FOUND);
        }

        return $this->json($event);
    }

    #[Route('/api/events/{id}/registrations', name: 'api_events_registrations_create', methods: ['POST'])]
    public function registration(
        int $id,
        Request $request,
        ValidatorInterface $validator,
        ValidationErrorFormatter $validationErrorFormatter,
        RegisterForEventHandler $handler,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $dto = RegisterForEventDto::fromArray(is_array($data) ? $data : []);

        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            return $this->json([
                'errors' => $validationErrorFormatter->format($errors),
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $registration = $handler->handle($id, $dto);
        } catch (EventNotPublishedException) {
            return $this->json([
                'message' => 'Event is not open for registration',
            ], Response::HTTP_CONFLICT);
        } catch (EventCapacityExceededException) {
            return $this->json([
                'message' => 'Event is full',
            ], Response::HTTP_CONFLICT);
        } catch (DuplicateRegistrationDetectedException) {
            return $this->json([
                'message' => 'Email already registered for the event',
            ], Response::HTTP_CONFLICT);
        }

        if (null === $registration) {
            return $this->json([
                'message' => 'No event found with ID: '.$id,
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'message' => 'Registration created',
            'id' => $registration->getId(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/events/{id}/cancel', name: 'api_events_cancel', methods: ['POST'])]
    public function cancel(
        int $id,
        CancelEventHandler $handler,
    ): JsonResponse {
        return $this->handleEventTransition($id, $handler, 'Event cancelled', 'Event cannot be cancelled');
    }

    #[Route('/api/events/{id}/complete', name: 'api_events_complete', methods: ['POST'])]
    public function complete(
        int $id,
        CompleteEventHandler $handler,
    ): JsonResponse {
        return $this->handleEventTransition($id, $handler, 'Event completed', 'Event cannot be completed');
    }

    #[Route('/api/events/{id}/publish', name: 'api_events_publish', methods: ['POST'])]
    public function publish(
        int $id,
        PublishEventHandler $handler,
    ): JsonResponse {
        return $this->handleEventTransition($id, $handler, 'Event published', 'Event cannot be published');
    }

    private function handleEventTransition(
        int $eventId,
        EventTransitionHandler $handler,
        string $successMessage,
        string $errorMessage,
    ): JsonResponse {
        try {
            $event = $handler->handle($eventId);
        } catch (EventCannotBeCancelledException|EventCannotBeCompletedException|EventCannotBePublishedException) {
            return $this->json([
                'message' => $errorMessage,
            ], Response::HTTP_CONFLICT);
        }

        if (null === $event) {
            return $this->json([
                'message' => 'No event found with ID: '.$eventId,
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'message' => $successMessage,
            'id' => $event->getId(),
        ], Response::HTTP_OK);
    }
}
