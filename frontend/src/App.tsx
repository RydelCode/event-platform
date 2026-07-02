import { useCallback, useEffect, useState, useRef } from "react";
import { useSearchParams } from "react-router-dom";
import { EventCreateForm } from "./components/EventCreateForm";
import { fetchEvents, type EventListItem, type PaginationMeta } from "./api/events";
import { EventList } from "./components/EventList";
import { EventFilters } from "./components/EventFilters";
import { Pagination } from "./components/Pagination";
import { ErrorState } from "./components/ErrorState";
import { LoadingState } from "./components/LoadingState";
import { ApiError, isRequestCanceled } from "./api/client";
import {
  DEFAULT_EVENT_FILTERS,
  EVENT_SORT_FIELD_OPTIONS,
  EVENT_STATUS_OPTIONS,
  isOptionValue,
  SORT_DIRECTION_OPTIONS,
} from "./constants/eventListOptions.ts";

const defaultMeta: PaginationMeta = { page: 1, limit: 10, total: 0, pages: 1 };

function App() {
  const latestRequestId = useRef(0);
  const [searchParams, setSearchParams] = useSearchParams();

  const page = Number(searchParams.get("page") ?? "1");
  const limit = Number(searchParams.get("limit") ?? "10");
  const statusParam = searchParams.get("status") ?? "";
  const status = isOptionValue(EVENT_STATUS_OPTIONS, statusParam) ? statusParam : "";
  const startsAfter = searchParams.get("startsAfter") ?? "";
  const sortParam = searchParams.get("sort") ?? "";
  const sort = isOptionValue(EVENT_SORT_FIELD_OPTIONS, sortParam)
    ? sortParam
    : DEFAULT_EVENT_FILTERS.sort;
  const directionParam = searchParams.get("direction") ?? "";
  const direction = isOptionValue(SORT_DIRECTION_OPTIONS, directionParam)
    ? directionParam
    : DEFAULT_EVENT_FILTERS.direction;

  const [events, setEvents] = useState<EventListItem[]>([]);
  const [meta, setMeta] = useState<PaginationMeta>(defaultMeta);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const loadEvents = useCallback(
    async (signal?: AbortSignal): Promise<void> => {
      const requestId = ++latestRequestId.current;

      setIsLoading(true);
      setError(null);

      try {
        const response = await fetchEvents(
          {
            page,
            limit,
            status: status || undefined,
            startsAfter: startsAfter || undefined,
            sort,
            direction: direction,
          },
          signal,
        );

        if (requestId !== latestRequestId.current) {
          return;
        }

        setEvents(response.items);
        setMeta(response.meta);
      } catch (err) {
        if (requestId !== latestRequestId.current || isRequestCanceled(err)) {
          return;
        }
        if (err instanceof ApiError) {
          setError(err.message);
        } else {
          setError("Could not load events.");
        }
      } finally {
        if (requestId === latestRequestId.current) {
          setIsLoading(false);
        }
      }
    },
    [page, limit, status, startsAfter, sort, direction],
  );

  useEffect(() => {
    const controller = new AbortController();

    void loadEvents(controller.signal);

    return () => {
      controller.abort();
      latestRequestId.current += 1;
    };
  }, [loadEvents]);

  function updateParams(updates: Record<string, string>, resetPage = true): void {
    const next = new URLSearchParams(searchParams);

    for (const [key, value] of Object.entries(updates)) {
      if (value) {
        next.set(key, value);
      } else {
        next.delete(key);
      }
    }

    if (resetPage) {
      next.set("page", "1");
    }

    setSearchParams(next);
  }

  return (
    <main>
      <h1>Event Platform</h1>

      <div>
        <EventCreateForm onEventCreated={loadEvents} />
      </div>

      <EventFilters
        status={status}
        startsAfter={startsAfter}
        sort={sort}
        direction={direction}
        onApply={(value) =>
          updateParams({
            status: value.status,
            startsAfter: value.startsAfter,
            sort: value.sort,
            direction: value.direction,
          })
        }
        onReset={() =>
          updateParams({
            status: "",
            startsAfter: "",
            sort: "",
            direction: "",
          })
        }
      />

      {isLoading && <LoadingState message="Loading events..." />}

      {error && <ErrorState message={error} />}

      {!isLoading && !error && (
        <>
          <EventList events={events} />

          <Pagination
            meta={meta}
            onPageChange={(value) => updateParams({ page: String(value) }, false)}
            onLimitChange={(value) => updateParams({ limit: String(value) })}
          />
        </>
      )}
    </main>
  );
}

export default App;
