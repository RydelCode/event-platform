<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EventApiTest extends WebTestCase
{
    private const EVENT_CREATE_TEST_CASE = [

        'title' => 'Test Event',
        'description' => 'Created from functional test',
        'startsAt' => '2026-06-01T10:00:00+00:00',
        'endsAt' => '2026-06-01T15:00:00+00:00',
        'location' => 'Warsaw',
        'capacity' => 100,
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
        self::assertSame(self::EVENT_CREATE_TEST_CASE['description'], $eventDetails['description']);
        self::assertSame(self::EVENT_CREATE_TEST_CASE['startsAt'], $eventDetails['startsAt']);
        self::assertSame(self::EVENT_CREATE_TEST_CASE['endsAt'], $eventDetails['endsAt']);
        self::assertSame(self::EVENT_CREATE_TEST_CASE['location'], $eventDetails['location']);
        self::assertSame(self::EVENT_CREATE_TEST_CASE['capacity'], $eventDetails['capacity']);
    }


    public function testCreatedEventsAreReturnedInListOrderedByStartDate(): void
    {
        $client = static::createClient();

        $laterEvent = $this->createEvent($client, [
            ...self::EVENT_CREATE_TEST_CASE,
            'title' => 'Later Test Event',
            'startsAt' => '2026-06-03T10:00:00+00:00',
            'endsAt' => '2026-06-03T15:00:00+00:00',
        ]);
        $earlierEvent = $this->createEvent($client, [
            ...self::EVENT_CREATE_TEST_CASE,
            'title' => 'Earlier Test Event',
            'startsAt' => '2026-06-02T10:00:00+00:00',
            'endsAt' => '2026-06-02T15:00:00+00:00',
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

    private function createEvent(KernelBrowser $client, array $payload): array
    {
        $this->postJson($client, '/api/events', $payload);

        self::assertResponseStatusCodeSame(201);

        return $this->decodeJsonResponse($client);
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
