import { Link } from "react-router-dom";
import { type EventListItem } from "../api/events";
import { routes } from "../routes/routes";
import { formatDate } from "../utils/date.ts";

type EventListProps = {
  events: EventListItem[];
};

export function EventList({ events }: EventListProps) {
  if (events.length === 0) {
    return <p>No events match the selected filters.</p>;
  }

  return (
    <ul className="event-list">
      {events.map((event) => (
        <li className="event-card" key={event.id}>
          <h2>{event.title}</h2>

          <p>{event.description}</p>

          <p>
            <strong>Status:</strong> {event.status}
          </p>

          <p>
            <strong>Starts at:</strong> {formatDate(event.startsAt)}
          </p>

          <p>
            <strong>Ends at:</strong> {formatDate(event.endsAt)}
          </p>

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
