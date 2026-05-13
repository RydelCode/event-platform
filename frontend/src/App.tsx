import { useEffect, useState } from "react";
import { EventCreateForm } from "./components/EventCreateForm";
import { fetchEvents, type EventItem } from "./api/events";
import { EventList } from "./components/EventList";

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
    } catch {
      setError("Could not load events.");
    } finally {
      setIsLoading(false);
    }
  }

  useEffect(() => {
    void loadEvents();
  }, []);

  return (
    <main style={{ padding: "2rem", fontFamily: "sans-serif" }}>
      <h1>Event Platform</h1>

      <div>
        <EventCreateForm onEventCreated={loadEvents} />
      </div>

      {isLoading && <p>Loading events...</p>}

      {error && <p>{error}</p>}

      {!isLoading && !error && (
        <EventList events={events} />
      )}
    </main>
  );
}

export default App;
