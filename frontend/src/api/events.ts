import { apiClient } from "./client";
import { apiEndpoints } from "./endpoints";

export type EventItem = {
  id: number;
  title: string;
  description: string | null;
  startsAt: string;
  endsAt: string;
  location: string;
  capacity: number;
};

export type CreateEventPayload = {
  title: string;
  description: string;
  startsAt: string;
  endsAt: string;
  location: string;
  capacity: number;
};

export type CreateEventResponse = {
  message: string;
  id: number;
};

export type RegisterForEventPayload = {
  attendeeName: string;
  attendeeEmail: string;
};

export type RegisterForEventResponse = {
  message: string;
  id: number;
};

export async function fetchEvents(): Promise<EventItem[]> {
  const response = await apiClient.get<EventItem[]>(apiEndpoints.events);

  return response.data;
}

export async function fetchEvent(id: number): Promise<EventItem> {
  const response = await apiClient.get<EventItem>(apiEndpoints.eventDetails(id));

  return response.data;
}

export async function createEvent(payload: CreateEventPayload): Promise<CreateEventResponse> {
  const response = await apiClient.post(apiEndpoints.events, payload);

  return response.data;
}

export async function registerForEvent(
  eventId: number,
  payload: RegisterForEventPayload,
): Promise<RegisterForEventResponse> {
  const response = await apiClient.post(apiEndpoints.eventRegistration(eventId), payload);

  return response.data;
}
