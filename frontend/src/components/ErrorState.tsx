import { Link } from "react-router-dom";

type ErrorStateProps = {
  title?: string;
  message: string;
};

export function ErrorState({ title = "Something went wrong", message }: ErrorStateProps) {
  return (
    <main>
      <section className="state-card">
        <h1>{title}</h1>
        <p>{message}</p>

        <Link className="back-link" to="/">
          ← Back to homepage
        </Link>
      </section>
    </main>
  );
}
