import { useEffect, useState } from "react";
import { EventCreateForm } from "./components/EventCreateForm";
import { fetchEvents, type EventItem } from "./api/events";
import { EventList } from "./components/EventList";
import { ErrorState } from "./components/ErrorState";
import { LoadingState } from "./components/LoadingState";
import { ApiError } from "./api/client";

function App() {
  const [events, setEvents] = useState<EventItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  async function loadEvents(): Promise<void> {
    setIsLoading(true);
    setError(null);

    try {
      const events = await fetchEvents();

      setEvents(events);
    } catch (err) {
      if (err instanceof ApiError) {
        setError(err.message);
      } else {
        setError("Could not load events.");
      }
    } finally {
      setIsLoading(false);
    }
  }

  useEffect(() => {
    void loadEvents();
  }, []);

  return (
    <main>
      <h1>Event Platform</h1>

      <div>
        <EventCreateForm onEventCreated={loadEvents} />
      </div>

      {isLoading && <LoadingState message="Loading events..." />}

      {error && <ErrorState message={error} />}

      {!isLoading && !error && <EventList events={events} />}
    </main>
  );
}

export default App;
