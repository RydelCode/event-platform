type LoadingStateProps = {
  message?: string;
};

export function LoadingState({ message = "Loading..." }: LoadingStateProps) {
  return (
    <main>
      <section className="state-card">
        <p>{message}</p>
      </section>
    </main>
  );
}
