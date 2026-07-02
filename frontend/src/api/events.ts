import { apiClient } from "./client";
import { apiEndpoints } from "./endpoints";

export type EventStatus = "draft" | "published" | "cancelled" | "completed";
export type EventSortField = "startsAt" | "endsAt";
export type SortDirection = "asc" | "desc";

export type EventItem = {
  id: number;
  status: EventStatus;
  title: string;
  description: string | null;
  startsAt: string;
  endsAt: string;
  location: string;
  capacity: number;
};

export type EventListItem = {
  id: number;
  title: string;
  status: EventStatus;
  description: string | null;
  startsAt: string;
  endsAt: string;
  location: string;
  capacity: number;
};

export type EventListParams = {
  page?: number;
  limit?: number;
  status?: EventStatus;
  startsAfter?: string;
  sort?: EventSortField;
  direction?: SortDirection;
};

export type PaginationMeta = {
  page: number;
  limit: number;
  total: number;
  pages: number;
};

export type EventListResponse = {
  items: EventListItem[];
  meta: PaginationMeta;
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

export async function fetchEvents(
  params: EventListParams = {},
  signal?: AbortSignal,
): Promise<EventListResponse> {
  const response = await apiClient.get<EventListResponse>(apiEndpoints.events, { params, signal });

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
