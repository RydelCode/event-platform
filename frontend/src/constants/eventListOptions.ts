import type { EventSortField, EventStatus, SortDirection } from "../api/events";

export const EVENT_STATUS_OPTIONS = {
  draft: "Draft",
  published: "Published",
  cancelled: "Cancelled",
  completed: "Completed",
} satisfies Record<EventStatus, string>;

export const EVENT_SORT_FIELD_OPTIONS = {
  startsAt: "Starts at",
  endsAt: "Ends at",
} satisfies Record<EventSortField, string>;

export const SORT_DIRECTION_OPTIONS = {
  asc: "Ascending",
  desc: "Descending",
} satisfies Record<SortDirection, string>;

export const DEFAULT_EVENT_FILTERS = {
  sort: "startsAt",
  direction: "asc",
} satisfies {
  sort: EventSortField;
  direction: SortDirection;
};

export function isOptionValue<T extends string>(
  options: Record<T, string>,
  value: string,
): value is T {
  return Object.hasOwn(options, value);
}
