import { Route, Routes } from "react-router-dom";

import App from "../App";
import { EventDetailsPage } from "../pages/EventDetailsPage";
import { paths } from "./routes";
import { NotFoundPage } from "../pages/NotFoundPage";

export function AppRoutes() {
  return (
    <Routes>
      <Route path={paths.home} element={<App />} />
      <Route path={paths.eventDetails} element={<EventDetailsPage />} />
      <Route path="*" element={<NotFoundPage />} />
    </Routes>
  );
}
