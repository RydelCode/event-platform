import {
  DEFAULT_EVENT_FILTERS,
  EVENT_SORT_FIELD_OPTIONS,
  EVENT_STATUS_OPTIONS,
  isOptionValue,
  SORT_DIRECTION_OPTIONS,
} from "../constants/eventListOptions.ts";
import type { EventSortField, EventStatus, SortDirection } from "../api/events.ts";
import React, { useEffect } from "react";

type EventFiltersValues = {
  status: EventStatus | "";
  startsAfter: string;
  sort: EventSortField;
  direction: SortDirection;
};

type EventFiltersProps = EventFiltersValues & {
  onApply: (filters: EventFiltersValues) => void;
  onReset: () => void;
};

export function EventFilters({
  status,
  startsAfter,
  sort,
  direction,
  onApply,
  onReset,
}: EventFiltersProps) {
  const [draft, setDraft] = React.useState<EventFiltersValues>({
    status,
    startsAfter,
    sort,
    direction,
  });
  const resetValues: EventFiltersValues = {
    status: "",
    startsAfter: "",
    sort: DEFAULT_EVENT_FILTERS.sort,
    direction: DEFAULT_EVENT_FILTERS.direction,
  };

  function handleReset(): void {
    setDraft(resetValues);
    onReset();
  }

  useEffect(() => {
    setDraft({ status, startsAfter, sort, direction });
  }, [status, startsAfter, sort, direction]);

  return (
    <div className="event-filters">
      <div>
        <label htmlFor="status-filter">Status</label>
        <select
          id="status-filter"
          value={draft.status}
          onChange={(event) => {
            const value = event.target.value;

            if (value === "" || isOptionValue(EVENT_STATUS_OPTIONS, value)) {
              setDraft((current) => ({
                ...current,
                status: value,
              }));
            }
          }}
        >
          <option value="">All</option>
          {Object.entries(EVENT_STATUS_OPTIONS).map(([value, label]) => (
            <option key={value} value={value}>
              {label}
            </option>
          ))}
        </select>
      </div>

      <div>
        <label htmlFor="starts-after-filter">Starts after</label>
        <input
          id="starts-after-filter"
          type="date"
          value={draft.startsAfter}
          onChange={(e) => {
            setDraft((current) => ({
              ...current,
              startsAfter: e.target.value,
            }));
          }}
        />
      </div>

      <div>
        <label htmlFor="sort-field">Sort by</label>
        <select
          id="sort-field"
          value={draft.sort}
          onChange={(event) => {
            const value = event.target.value;

            if (isOptionValue(EVENT_SORT_FIELD_OPTIONS, value)) {
              setDraft((current) => ({
                ...current,
                sort: value,
              }));
            }
          }}
        >
          {Object.entries(EVENT_SORT_FIELD_OPTIONS).map(([value, label]) => (
            <option key={value} value={value}>
              {label}
            </option>
          ))}
        </select>
      </div>

      <div>
        <label htmlFor="sort-direction">Direction</label>
        <select
          id="sort-direction"
          value={draft.direction}
          onChange={(event) => {
            const value = event.target.value;

            if (isOptionValue(SORT_DIRECTION_OPTIONS, value)) {
              setDraft((current) => ({
                ...current,
                direction: value,
              }));
            }
          }}
        >
          {Object.entries(SORT_DIRECTION_OPTIONS).map(([value, label]) => (
            <option key={value} value={value}>
              {label}
            </option>
          ))}
        </select>
      </div>

      <div className="event-filter-actions">
        <button type="button" onClick={() => onApply(draft)}>
          Apply
        </button>
        <button className="event-filter-reset" type="button" onClick={handleReset}>
          Reset
        </button>
      </div>
    </div>
  );
}
