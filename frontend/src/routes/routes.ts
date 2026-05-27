export const routes = {
  home: "/",
  eventDetails: (id: number) => `/events/${id}`,
};

export const paths = {
  home: "/",
  eventDetails: "/events/:id",
};
