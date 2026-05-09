export function getErrorMessage(error) {
  const errors = error.response?.data?.errors;

  if (errors) {
    const firstError = Object.values(errors)[0]?.[0];

    if (firstError) {
      return firstError;
    }
  }

  return error.response?.data?.message || error.message || 'Something went wrong.';
}
