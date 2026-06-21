<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Domain\Event\EventStatus;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EventApiTest extends WebTestCase
{
    private const EVENT_CREATE_TEST_CASE = [
        'title' => 'Test Event',
        'description' => 'Created from functional test',
        'startsAt' => '2026-06-01T10:00',
        'endsAt' => '2026-06-01T15:00',
        'location' => 'Warsaw',
        'capacity' => 100,
    ];

    private const REGISTRATION_CREATE_TEST_CASE = [
        'attendeeName' => 'Test Attendee',
        'attendeeEmail' => 'test@mail.com',
    ];

    public function testListEventsReturnsSuccessfulResponse(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/events');

        self::assertResponseIsSuccessful();

        $data = $this->decodeJsonResponse($client);

        self::assertIsArray($data);
    }

    public function testMissingEventReturnsNotFound(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/events/9999999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testCreateEventReturnsCreatedResponse(): void
    {
        $client = static::createClient();

        $this->postJson($client, '/api/events', self::EVENT_CREATE_TEST_CASE);

        self::assertResponseStatusCodeSame(201);

        $data = $this->decodeJsonResponse($client);

        self::assertArrayHasKey('id', $data);
        self::assertIsInt($data['id']);
        self::assertSame('Event created', $data['message']);
    }

    public function testCreateEventWithInvalidPayloadReturnsBadRequest(): void
    {
        $client = static::createClient();

        $this->postJson($client, '/api/events', []);

        self::assertResponseStatusCodeSame(400);

        $this->postJson($client, '/api/events', [
            ...self::EVENT_CREATE_TEST_CASE,
            'startsAt' => 'Not a date, actually',
        ]);

        self::assertResponseStatusCodeSame(400);

        $this->postJson($client, '/api/events', [
            ...self::EVENT_CREATE_TEST_CASE,
            'endsAt' => 'Not a date, actually too',
        ]);

        self::assertResponseStatusCodeSame(400);
    }

    public function testCreatedEventDetailsReturnsValidResponse(): void
    {
        $client = static::createClient();

        $createdEventData = $this->createEvent($client, self::EVENT_CREATE_TEST_CASE);

        $client->request('GET', sprintf('/api/events/%d', $createdEventData['id']));

        self::assertResponseIsSuccessful();

        $eventDetails = $this->decodeJsonResponse($client);

        self::assertSame($createdEventData['id'], $eventDetails['id']);
        self::assertSame(self::EVENT_CREATE_TEST_CASE['title'], $eventDetails['title']);
        self::assertSame(EventStatus::DRAFT->value, $eventDetails['status']);
        self::assertSame(self::EVENT_CREATE_TEST_CASE['description'], $eventDetails['description']);
        self::assertSame(self::EVENT_CREATE_TEST_CASE['startsAt'], $eventDetails['startsAt']);
        self::assertSame(self::EVENT_CREATE_TEST_CASE['endsAt'], $eventDetails['endsAt']);
        self::assertSame(self::EVENT_CREATE_TEST_CASE['location'], $eventDetails['location']);
        self::assertSame(self::EVENT_CREATE_TEST_CASE['capacity'], $eventDetails['capacity']);
    }

    public function testPublishEventReturnsValidResponse(): void
    {
        $client = static::createClient();

        $createdEventData = $this->createEvent($client, self::EVENT_CREATE_TEST_CASE);
        $this->publishEvent($client, $createdEventData['id']);

        self::assertResponseIsSuccessful();
    }

    public function testCancelDraftEventReturnsValidResponse(): void
    {
        $client = static::createClient();

        $createdEventData = $this->createEvent($client, self::EVENT_CREATE_TEST_CASE);
        $this->cancelEvent($client, $createdEventData['id']);

        self::assertResponseIsSuccessful();
    }

    public function testCancelPublishedEventReturnsValidResponse(): void
    {
        $client = static::createClient();

        $createdEventData = $this->createEvent($client, self::EVENT_CREATE_TEST_CASE);
        $this->publishEvent($client, $createdEventData['id']);
        $this->cancelEvent($client, $createdEventData['id']);

        self::assertResponseIsSuccessful();
    }

    public function testCancelCompletedEventReturnsConflict(): void
    {
        $client = static::createClient();

        $createdEventData = $this->createEvent($client, self::EVENT_CREATE_TEST_CASE);
        $this->publishEvent($client, $createdEventData['id']);

        $this->completeEvent($client, $createdEventData['id']);

        $this->postJson($client, sprintf('/api/events/%d/cancel', $createdEventData['id']), []);
        self::assertResponseStatusCodeSame(409);
    }

    public function testCompletePublishedEventReturnsValidResponse(): void
    {
        $client = static::createClient();

        $createdEventData = $this->createEvent($client, self::EVENT_CREATE_TEST_CASE);
        $this->publishEvent($client, $createdEventData['id']);
        $this->completeEvent($client, $createdEventData['id']);

        self::assertResponseIsSuccessful();
    }

    public function testCompleteDraftEventReturnsConflict(): void
    {
        $client = static::createClient();

        $createdEventData = $this->createEvent($client, self::EVENT_CREATE_TEST_CASE);

        $this->postJson($client, sprintf('/api/events/%d/complete', $createdEventData['id']), []);

        self::assertResponseStatusCodeSame(409);
    }

    public function testCompleteCancelledEventReturnsConflict(): void
    {
        $client = static::createClient();

        $createdEventData = $this->createEvent($client, self::EVENT_CREATE_TEST_CASE);

        $this->cancelEvent($client, $createdEventData['id']);

        $this->postJson($client, sprintf('/api/events/%d/complete', $createdEventData['id']), []);

        self::assertResponseStatusCodeSame(409);
    }

    public function testRegisterForCancelledEventReturnsConflict(): void
    {
        $client = static::createClient();

        $createdDraftEventData = $this->createEvent($client, [...self::EVENT_CREATE_TEST_CASE]);

        $this->cancelEvent($client, $createdDraftEventData['id']);

        $this->postJson($client, sprintf('/api/events/%d/registrations', $createdDraftEventData['id']), self::REGISTRATION_CREATE_TEST_CASE);

        $this->assertResponseStatusCodeSame(409);
    }

    public function testRegisterForCompleteEventReturnsConflict(): void
    {
        $client = static::createClient();

        $createdDraftEventData = $this->createEvent($client, [...self::EVENT_CREATE_TEST_CASE]);

        $this->publishEvent($client, $createdDraftEventData['id']);
        $this->completeEvent($client, $createdDraftEventData['id']);

        $this->postJson($client, sprintf('/api/events/%d/registrations', $createdDraftEventData['id']), self::REGISTRATION_CREATE_TEST_CASE);

        $this->assertResponseStatusCodeSame(409);
    }

    public function testRegisterForDraftEventReturnsConflict(): void
    {
        $client = static::createClient();

        $createdDraftEventData = $this->createEvent($client, [...self::EVENT_CREATE_TEST_CASE]);

        $this->postJson($client, sprintf('/api/events/%d/registrations', $createdDraftEventData['id']), self::REGISTRATION_CREATE_TEST_CASE);

        $this->assertResponseStatusCodeSame(409);
    }

    public function testCannotPublishNotDraftEvent(): void
    {
        $client = static::createClient();

        $createdPublishedEventData = $this->createEvent($client, [...self::EVENT_CREATE_TEST_CASE]);

        $this->publishEvent($client, $createdPublishedEventData['id']);

        $this->postJson($client, sprintf('/api/events/%d/publish', $createdPublishedEventData['id']), []);

        $this->assertResponseStatusCodeSame(409);
    }

    public function testCreatedEventsAreReturnedInListOrderedByStartDate(): void
    {
        $client = static::createClient();

        $laterEvent = $this->createEvent($client, [
            ...self::EVENT_CREATE_TEST_CASE,
            'title' => 'Later Test Event',
            'startsAt' => '2026-06-03T10:00',
            'endsAt' => '2026-06-03T15:00',
        ]);
        $earlierEvent = $this->createEvent($client, [
            ...self::EVENT_CREATE_TEST_CASE,
            'title' => 'Earlier Test Event',
            'startsAt' => '2026-06-02T10:00',
            'endsAt' => '2026-06-02T15:00',
        ]);

        $client->request('GET', '/api/events');

        self::assertResponseIsSuccessful();

        $events = $this->decodeJsonResponse($client);
        $ids = array_column($events, 'id');
        self::assertContains($laterEvent['id'], $ids);
        self::assertContains($earlierEvent['id'], $ids);
        self::assertLessThan(
            array_search($laterEvent['id'], $ids, true),
            array_search($earlierEvent['id'], $ids, true)
        );
    }

    public function testCreateRegistrationReturnsCreatedResponse(): void
    {
        $client = static::createClient();

        $createdEventData = $this->createEvent($client, self::EVENT_CREATE_TEST_CASE);
        $this->publishEvent($client, $createdEventData['id']);

        $this->postJson($client, sprintf('/api/events/%d/registrations', $createdEventData['id']), self::REGISTRATION_CREATE_TEST_CASE);

        self::assertResponseStatusCodeSame(201);

        $data = $this->decodeJsonResponse($client);

        self::assertArrayHasKey('id', $data);
        self::assertIsInt($data['id']);
        self::assertSame('Registration created', $data['message']);
    }

    public function testCreateRegistrationMissingEventReturnsNotFound(): void
    {
        $client = static::createClient();

        $this->postJson($client, '/api/events/9999999/registrations', self::REGISTRATION_CREATE_TEST_CASE);

        self::assertResponseStatusCodeSame(404);
    }

    public function testCreateRegistrationWithInvalidPayloadReturnsBadRequest(): void
    {
        $client = static::createClient();

        $createdEventData = $this->createEvent($client, self::EVENT_CREATE_TEST_CASE);
        $this->publishEvent($client, $createdEventData['id']);

        $this->postJson($client, sprintf('/api/events/%d/registrations', $createdEventData['id']), []);

        self::assertResponseStatusCodeSame(400);
    }

    public function testCreateRegistrationForFullEventReturnsConflict(): void
    {
        $client = static::createClient();

        $createdEventData = $this->createEvent($client, [...self::EVENT_CREATE_TEST_CASE, 'capacity' => 1]);
        $this->publishEvent($client, $createdEventData['id']);

        $this->postJson($client, sprintf('/api/events/%d/registrations', $createdEventData['id']), self::REGISTRATION_CREATE_TEST_CASE);

        self::assertResponseStatusCodeSame(201);

        $this->postJson($client, sprintf('/api/events/%d/registrations', $createdEventData['id']), [...self::REGISTRATION_CREATE_TEST_CASE, 'attendeeEmail' => 'test2@mail.com']);

        $response = $this->decodeJsonResponse($client);

        self::assertResponseStatusCodeSame(409);
        self::assertSame('Event is full', $response['message']);
    }

    public function testCreateRegistrationForSameEventWithDuplicateEmailReturnsConflict(): void
    {
        $client = static::createClient();

        $createdEventData = $this->createEvent($client, [...self::EVENT_CREATE_TEST_CASE]);
        $this->publishEvent($client, $createdEventData['id']);

        $this->postJson($client, sprintf('/api/events/%d/registrations', $createdEventData['id']), self::REGISTRATION_CREATE_TEST_CASE);

        self::assertResponseStatusCodeSame(201);

        $this->postJson($client, sprintf('/api/events/%d/registrations', $createdEventData['id']), [...self::REGISTRATION_CREATE_TEST_CASE]);

        $response = $this->decodeJsonResponse($client);

        self::assertResponseStatusCodeSame(409);
        self::assertSame('Email already registered for the event', $response['message']);
    }

    private function createEvent(KernelBrowser $client, array $payload): array
    {
        $this->postJson($client, '/api/events', $payload);

        self::assertResponseStatusCodeSame(201);

        return $this->decodeJsonResponse($client);
    }

    private function cancelEvent(KernelBrowser $client, int $eventId): void
    {
        $this->postJson($client, sprintf('/api/events/%d/cancel', $eventId), []);

        self::assertResponseIsSuccessful();
    }

    private function completeEvent(KernelBrowser $client, int $eventId): void
    {
        $this->postJson($client, sprintf('/api/events/%d/complete', $eventId), []);

        self::assertResponseIsSuccessful();
    }

    private function publishEvent(KernelBrowser $client, int $eventId): void
    {
        $this->postJson($client, sprintf('/api/events/%d/publish', $eventId), []);

        self::assertResponseIsSuccessful();
    }

    private function postJson(KernelBrowser $client, string $uri, array $payload): void
    {
        $client->request(
            'POST',
            $uri,
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    private function decodeJsonResponse(KernelBrowser $client): array
    {
        return json_decode(
            $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}
