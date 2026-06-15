import React, { useState } from "react";

import { registerForEvent, type RegisterForEventPayload } from "../api/events";
import { ApiError } from "../api/client";
import { hasValidationErrors } from "../utils/validation";
import { ValidationErrors } from "./ValidationErrors";

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
    } catch (err) {
      if (err instanceof ApiError) {
        setErrorMessage(err.message);
        setValidationErrors(err.errors ?? {});
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
        <ValidationErrors errors={validationErrors.attendeeName} />
      </div>

      <div>
        <label htmlFor="attendeeEmail">Email</label>

        <input
          id="attendeeEmail"
          type="email"
          value={formData.attendeeEmail}
          onChange={handleChange}
        />
        <ValidationErrors errors={validationErrors.attendeeEmail} />
      </div>

      <button type="submit" disabled={isSubmitting}>
        {isSubmitting ? "Registering..." : "Register for event"}
      </button>

      {errorMessage && !hasValidationErrors(validationErrors) && <p>{errorMessage}</p>}
      {successMessage && <p>{successMessage}</p>}
    </form>
  );
}
