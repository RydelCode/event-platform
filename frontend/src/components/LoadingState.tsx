type LoadingStateProps = {
  message?: string;
};

export function LoadingState({ message = "Loading..." }: LoadingStateProps) {
  return (
    <main>
      <p>{message}</p>
    </main>
  );
}
