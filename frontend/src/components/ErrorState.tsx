type ErrorStateProps = {
  title?: string;
  message: string;
};

export function ErrorState({ title = "Something went wrong", message }: ErrorStateProps) {
  return (
    <main>
      <h1>{title}</h1>
      <p>{message}</p>
    </main>
  );
}
