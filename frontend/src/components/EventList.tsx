import { type EventItem } from "../api/events";

type EventListProps = {
  events: EventItem[];
};

export function EventList({ events }: EventListProps) {

  if (events.length === 0) {
    return (
      <p>No events available.</p>
    )
  }

  return (
    <ul>
      {events.map((event) => (
        <li key={event.id}>
          <h2>{event.title}</h2>

          <p>{event.description}</p>

          <p>
            <strong>Location:</strong> {event.location}
          </p>

          <p>
            <strong>Capacity:</strong> {event.capacity}
          </p>
        </li>
      ))}
    </ul>
  );
}
