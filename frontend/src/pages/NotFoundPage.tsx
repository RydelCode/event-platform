import { ErrorState } from "../components/ErrorState";

export function NotFoundPage() {
  return <ErrorState title="404" message="The page you are looking for does not exist." />;
}
