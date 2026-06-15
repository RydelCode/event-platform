import { Link } from "react-router-dom";
import { type EventItem } from "../api/events";
import { routes } from "../routes/routes";

type EventListProps = {
  events: EventItem[];
};

export function EventList({ events }: EventListProps) {
  if (events.length === 0) {
    return <p>No events available.</p>;
  }

  return (
    <ul className="event-list">
      {events.map((event) => (
        <li className="event-card" key={event.id}>
          <h2>{event.title}</h2>

          <p>{event.description}</p>

          <p>
            <strong>Location:</strong> {event.location}
          </p>

          <p>
            <strong>Capacity:</strong> {event.capacity}
          </p>

          <Link className="button-link" to={routes.eventDetails(event.id)}>
            View details
          </Link>
        </li>
      ))}
    </ul>
  );
}
