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

export async function fetchEvents(): Promise<EventItem[]> {
  const response = await apiClient.get<EventItem[]>(apiEndpoints.events);

  return response.data;
}

export async function fetchEvent(id: number): Promise<EventItem> {
  const response = await apiClient.get<EventItem>(
    apiEndpoints.eventDetails(id),
  );

  return response.data;
}

export async function createEvent(payload: CreateEventPayload): Promise<void> {
  await apiClient.post(apiEndpoints.events, payload);
}
