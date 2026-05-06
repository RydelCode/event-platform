import { useEffect, useState } from 'react';
import axios from 'axios';

type Event = {
  id: number;
  title: string;
  description: string | null;
  startsAt: string;
  endsAt: string;
  location: string;
  capacity: number;
};

function App() {
  const [events, setEvents] = useState<Event[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    axios
      .get('http://localhost:8000/api/events')
      .then((response) => {
        setEvents(response.data);
      })
      .finally(() => {
        setLoading(false);
      });
  }, []);

  if (loading) {
    return <p>Loading events...</p>;
  }

  return (
    <main style={{ padding: '2rem', fontFamily: 'sans-serif' }}>
      <h1>Event Platform</h1>

      {events.length === 0 ? (
        <p>No events available.</p>
      ) : (
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
      )}
    </main>
  );
}

export default App;