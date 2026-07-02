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

        self::assertArrayHasKey('items', $data);
        self::assertArrayHasKey('meta', $data);
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

        $response = $this->decodeJsonResponse($client);
        $ids = array_column($response['items'], 'id');
        self::assertContains($laterEvent['id'], $ids);
        self::assertContains($earlierEvent['id'], $ids);
        self::assertLessThan(
            array_search($laterEvent['id'], $ids, true),
            array_search($earlierEvent['id'], $ids, true)
        );
    }

    public function testListEventsFiltersByStatus(): void
    {
        $client = static::createClient();

        $draftEvent = $this->createEvent($client, [
            ...self::EVENT_CREATE_TEST_CASE,
            'title' => 'Draft Event',
        ]);
        $publishedEvent = $this->createEvent($client, [
            ...self::EVENT_CREATE_TEST_CASE,
            'title' => 'Published Event',
        ]);
        $this->publishEvent($client, $publishedEvent['id']);

        $client->request('GET', '/api/events?status=published');

        self::assertResponseIsSuccessful();

        $response = $this->decodeJsonResponse($client);
        $ids = array_column($response['items'], 'id');

        self::assertContains($publishedEvent['id'], $ids);
        self::assertNotContains($draftEvent['id'], $ids);
    }

    public function testListEventsWithInvalidStatusReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/events?status=not-a-status');

        self::assertResponseStatusCodeSame(400);
    }

    public function testListEventsFiltersByStartsAfter(): void
    {
        $client = static::createClient();

        $earlierEvent = $this->createEvent($client, [
            ...self::EVENT_CREATE_TEST_CASE,
            'title' => 'Earlier Event',
            'startsAt' => '2026-06-01T10:00',
            'endsAt' => '2026-06-01T15:00',
        ]);
        $laterEvent = $this->createEvent($client, [
            ...self::EVENT_CREATE_TEST_CASE,
            'title' => 'Later Event',
            'startsAt' => '2026-06-05T10:00',
            'endsAt' => '2026-06-05T15:00',
        ]);

        $client->request('GET', '/api/events?startsAfter=2026-06-02');

        self::assertResponseIsSuccessful();

        $response = $this->decodeJsonResponse($client);
        $ids = array_column($response['items'], 'id');

        self::assertContains($laterEvent['id'], $ids);
        self::assertNotContains($earlierEvent['id'], $ids);
    }

    public function testListEventsWithInvalidStartsAfterReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/events?startsAfter=not-a-date');

        self::assertResponseStatusCodeSame(400);
    }

    public function testListEventsSortsByEndsAtDescending(): void
    {
        $client = static::createClient();

        $earlierEvent = $this->createEvent($client, [
            ...self::EVENT_CREATE_TEST_CASE,
            'title' => 'Earlier Event',
            'startsAt' => '2026-06-01T10:00',
            'endsAt' => '2026-06-01T15:00',
        ]);
        $laterEvent = $this->createEvent($client, [
            ...self::EVENT_CREATE_TEST_CASE,
            'title' => 'Later Event',
            'startsAt' => '2026-06-05T10:00',
            'endsAt' => '2026-06-05T15:00',
        ]);

        $client->request('GET', '/api/events?sort=endsAt&direction=desc');

        self::assertResponseIsSuccessful();

        $response = $this->decodeJsonResponse($client);
        $ids = array_column($response['items'], 'id');

        self::assertLessThan(
            array_search($earlierEvent['id'], $ids, true),
            array_search($laterEvent['id'], $ids, true)
        );
    }

    public function testListEventsWithInvalidSortReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/events?sort=notAField');

        self::assertResponseStatusCodeSame(400);
    }

    public function testListEventsWithInvalidOrderReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/events?direction=sideways');

        self::assertResponseStatusCodeSame(400);
    }

    public function testListEventsPaginatesResults(): void
    {
        $client = static::createClient();

        for ($i = 0; $i < 3; ++$i) {
            $this->createEvent($client, [
                ...self::EVENT_CREATE_TEST_CASE,
                'title' => 'Paginated Event '.$i,
                'startsAt' => sprintf('2026-07-0%dT10:00', $i + 1),
                'endsAt' => sprintf('2026-07-0%dT15:00', $i + 1),
            ]);
        }

        $client->request('GET', '/api/events?page=1&limit=2');

        self::assertResponseIsSuccessful();

        $page1 = $this->decodeJsonResponse($client);

        self::assertCount(2, $page1['items']);
        self::assertSame('Paginated Event 0', $page1['items'][0]['title']);
        self::assertSame('Paginated Event 1', $page1['items'][1]['title']);

        self::assertSame(1, $page1['meta']['page']);
        self::assertSame(2, $page1['meta']['limit']);
        self::assertSame(3, $page1['meta']['total']);
        self::assertSame(2, $page1['meta']['pages']);

        $client->request('GET', '/api/events?page=2&limit=2');

        self::assertResponseIsSuccessful();

        $page2 = $this->decodeJsonResponse($client);

        self::assertCount(1, $page2['items']);
        self::assertSame('Paginated Event 2', $page2['items'][0]['title']);

        self::assertSame(2, $page2['meta']['page']);
        self::assertSame(2, $page2['meta']['limit']);
        self::assertSame(3, $page2['meta']['total']);
        self::assertSame(2, $page2['meta']['pages']);
    }

    public function testListEventsWithInvalidPageReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/events?page=0');

        self::assertResponseStatusCodeSame(400);
    }

    public function testListEventsWithLimitOutOfRangeReturnsBadRequest(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/events?limit=100');

        self::assertResponseStatusCodeSame(400);
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

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
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

    /**
     * @param array<string, mixed> $payload
     */
    private function postJson(KernelBrowser $client, string $uri, array $payload): void
    {
        $client->request(
            'POST',
            $uri,
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonResponse(KernelBrowser $client): array
    {
        $content = $client->getResponse()->getContent();

        self::assertIsString($content);

        return json_decode(
            $content,
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}
