type ValidationErrorsProps = {
  errors?: string[];
};

export function ValidationErrors({ errors }: ValidationErrorsProps) {
  if (!errors?.length) {
    return null;
  }

  return (
    <>
      {errors?.map((message) => (
        <p className="validation-error" key={message}>
          {message}
        </p>
      ))}
    </>
  );
}
