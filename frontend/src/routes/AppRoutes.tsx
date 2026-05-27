import { Route, Routes } from "react-router-dom";

import App from "../App";
import { EventDetailsPage } from "../pages/EventDetailsPage";
import { paths } from "./routes";

export function AppRoutes() {
  return (
    <Routes>
      <Route path={paths.home} element={<App />} />
      <Route path={paths.eventDetails} element={<EventDetailsPage />} />
    </Routes>
  );
}
