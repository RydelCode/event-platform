export const apiEndpoints = {
  events: "/api/events",
  eventDetails: (id: number) => `/api/events/${id}`,
  eventRegistration: (id: number) => `/api/events/${id}/registrations`,
};
