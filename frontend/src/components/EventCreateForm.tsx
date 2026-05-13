import React, { useState } from "react";

import { createEvent, type CreateEventPayload } from "../api/events";

type EventCreateFormProps = {
  onEventCreated: () => void;
};

const initialFormData: CreateEventPayload = {
  title: "",
  description: "",
  startsAt: "",
  endsAt: "",
  location: "",
  capacity: 1,
};

export function EventCreateForm({ onEventCreated }: EventCreateFormProps) {
  const [formData, setFormData] = useState<CreateEventPayload>(initialFormData);

  const [isSubmitting, setIsSubmitting] = useState(false);

  const [error, setError] = useState<string | null>(null);

  async function handleSubmit(
    event: React.FormEvent<HTMLFormElement>,
  ): Promise<void> {
    event.preventDefault();

    setIsSubmitting(true);

    setError(null);

    try {
      await createEvent(formData);

      onEventCreated();

      setFormData(initialFormData);
    } catch {
      setError("Could not create event.");
    } finally {
      setIsSubmitting(false);
    }
  }

  function handleChange(
    event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>,
  ) {
    const { id, value } = event.target;

    setFormData((prev) => ({
      ...prev,
      [id]: id === "capacity" ? Number(value) : value,
    }));
  }

  return (
    <form onSubmit={handleSubmit}>
      <h2>Create event</h2>

      {error && <p>{error}</p>}

      <div>
        <label htmlFor="title">Title</label>

        <input
          id="title"
          type="text"
          value={formData.title}
          onChange={handleChange}
        />
      </div>

      <div>
        <label htmlFor="description">Description</label>

        <textarea
          id="description"
          value={formData.description}
          onChange={handleChange}
        />
      </div>

      <div>
        <label htmlFor="startsAt">Starts At</label>

        <input
          id="startsAt"
          type="datetime-local"
          value={formData.startsAt}
          onChange={handleChange}
        />
      </div>

      <div>
        <label htmlFor="endsAt">Ends At</label>

        <input
          id="endsAt"
          type="datetime-local"
          value={formData.endsAt}
          onChange={handleChange}
        />
      </div>

      <div>
        <label htmlFor="location">Location</label>

        <input
          id="location"
          type="text"
          value={formData.location}
          onChange={handleChange}
        />
      </div>

      <div>
        <label htmlFor="capacity">Capacity</label>

        <input
          id="capacity"
          type="number"
          value={formData.capacity}
          onChange={handleChange}
        />
      </div>

      <button type="submit" disabled={isSubmitting}>
        {isSubmitting ? "Creating..." : "Create event"}
      </button>
    </form>
  );
}
