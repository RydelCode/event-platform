import React, { useState } from "react";

import { registerForEvent, type RegisterForEventPayload } from "../api/events";
import { ApiError } from "../api/client";

type EventRegistrationFormProps = {
  eventId: number;
};

const initialFormData: RegisterForEventPayload = {
  attendeeEmail: "",
  attendeeName: "",
};

export function EventRegistrationForm({ eventId }: EventRegistrationFormProps) {
  const [formData, setFormData] = useState<RegisterForEventPayload>(initialFormData);
  const [successMessage, setSuccessMessage] = useState<string | null>(null);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [validationErrors, setValidationErrors] = useState<Record<string, string[]>>({});
  const [isSubmitting, setIsSubmitting] = useState<boolean>(false);

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>): Promise<void> {
    event.preventDefault();

    setIsSubmitting(true);
    setErrorMessage(null);
    setValidationErrors({});
    setSuccessMessage(null);

    try {
      const response = await registerForEvent(eventId, formData);

      setSuccessMessage(response.message);
      setFormData(initialFormData);
    } catch (error) {
      if (error instanceof ApiError) {
        setErrorMessage(error.message);
        setValidationErrors(error.errors ?? {});
        return;
      }

      setErrorMessage("Could not register for event.");
    } finally {
      setIsSubmitting(false);
    }
  }

  function handleChange(event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) {
    const { id, value } = event.target;

    setFormData((prev) => ({
      ...prev,
      [id]: value,
    }));
  }

  return (
    <form onSubmit={handleSubmit}>
      <h2>Register for event</h2>

      <div>
        <label htmlFor="attendeeName">Name</label>

        <input
          id="attendeeName"
          type="text"
          value={formData.attendeeName}
          onChange={handleChange}
        />
        {validationErrors.attendeeName?.map((message) => (
          <p key={message}>{message}</p>
        ))}
      </div>

      <div>
        <label htmlFor="attendeeEmail">Email</label>

        <input
          id="attendeeEmail"
          type="email"
          value={formData.attendeeEmail}
          onChange={handleChange}
        />
        {validationErrors.attendeeEmail?.map((message) => (
          <p key={message}>{message}</p>
        ))}
      </div>

      <button type="submit" disabled={isSubmitting}>
        {isSubmitting ? "Registering..." : "Register for event"}
      </button>

      {errorMessage && <p>{errorMessage}</p>}
      {successMessage && <p>{successMessage}</p>}
    </form>
  );
}
