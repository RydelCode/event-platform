import React, { useState } from "react";

import { createEvent, type CreateEventPayload } from "../api/events";
import { ApiError } from "../api/client";
import { hasValidationErrors } from "../utils/validation";
import { ValidationErrors } from "./ValidationErrors";

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
  const [successMessage, setSuccessMessage] = useState<string | null>(null);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [validationErrors, setValidationErrors] = useState<Record<string, string[]>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>): Promise<void> {
    event.preventDefault();

    setIsSubmitting(true);
    setErrorMessage(null);
    setValidationErrors({});
    setSuccessMessage(null);

    try {
      const response = await createEvent(formData);

      onEventCreated();

      setSuccessMessage(response.message);
      setFormData(initialFormData);
    } catch (err) {
      if (err instanceof ApiError) {
        setErrorMessage(err.message);
        setValidationErrors(err.errors ?? {});
        return;
      }

      setErrorMessage("Could not create an event.");
    } finally {
      setIsSubmitting(false);
    }
  }

  function handleChange(event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) {
    const { id, value } = event.target;

    setFormData((prev) => ({
      ...prev,
      [id]: id === "capacity" ? Number(value) : value,
    }));
  }

  return (
    <form onSubmit={handleSubmit}>
      <h2>Create event</h2>

      <div>
        <label htmlFor="title">Title</label>

        <input id="title" type="text" value={formData.title} onChange={handleChange} />
        <ValidationErrors errors={validationErrors.title} />
      </div>

      <div>
        <label htmlFor="description">Description</label>

        <textarea id="description" value={formData.description} onChange={handleChange} />
      </div>

      <div>
        <label htmlFor="startsAt">Starts At</label>

        <input
          id="startsAt"
          type="datetime-local"
          value={formData.startsAt}
          onChange={handleChange}
        />
        <ValidationErrors errors={validationErrors.startsAt} />
      </div>

      <div>
        <label htmlFor="endsAt">Ends At</label>

        <input id="endsAt" type="datetime-local" value={formData.endsAt} onChange={handleChange} />
        <ValidationErrors errors={validationErrors.endsAt} />
      </div>

      <div>
        <label htmlFor="location">Location</label>

        <input id="location" type="text" value={formData.location} onChange={handleChange} />
        <ValidationErrors errors={validationErrors.location} />
      </div>

      <div>
        <label htmlFor="capacity">Capacity</label>

        <input id="capacity" type="number" value={formData.capacity} onChange={handleChange} />
        <ValidationErrors errors={validationErrors.capacity} />
      </div>

      <button type="submit" disabled={isSubmitting}>
        {isSubmitting ? "Creating..." : "Create event"}
      </button>

      {errorMessage && !hasValidationErrors(validationErrors) && (
        <p className="error-message">{errorMessage}</p>
      )}
      {successMessage && <p className="success-message">{successMessage}</p>}
    </form>
  );
}
