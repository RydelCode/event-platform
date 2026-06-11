import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { fetchEvent, type EventItem } from "../api/events";
import { formatDate } from "../utils/date";
import { EventRegistrationForm } from "../components/EventRegistrationForm";

export function EventDetailsPage() {
  const { id } = useParams();

  const [event, setEvent] = useState<EventItem | null>(null);

  const [isLoading, setIsLoading] = useState(true);

  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    async function loadEvent(): Promise<void> {
      if (!id) {
        setError("Missing event ID.");
        setIsLoading(false);

        return;
      }

      setError(null);

      try {
        const event = await fetchEvent(Number(id));

        setEvent(event);
      } catch {
        setError("Could not load event.");
      } finally {
        setIsLoading(false);
      }
    }

    void loadEvent();
  }, [id]);

  if (isLoading) {
    return <p>Loading event...</p>;
  }

  if (error) {
    return <p>{error}</p>;
  }

  if (!event) {
    return <p>Event not found.</p>;
  }

  return (
    <main style={{ padding: "2rem", fontFamily: "sans-serif" }}>
      <h1>{event.title}</h1>

      {event.description && <p>{event.description}</p>}

      <p>
        <strong>Starts at: </strong> {formatDate(event.startsAt)}
      </p>

      <p>
        <strong>Ends at: </strong> {formatDate(event.endsAt)}
      </p>

      <p>
        <strong>Location: </strong> {event.location}
      </p>

      <p>
        <strong>Capacity: </strong> {event.capacity}
      </p>

      <EventRegistrationForm eventId={event.id} />
    </main>
  );
}
