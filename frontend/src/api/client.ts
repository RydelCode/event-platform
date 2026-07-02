import axios from "axios";

type ApiErrorResponse = {
  message?: string;
  errors?: Record<string, string[]>;
};

export class ApiError extends Error {
  public readonly status?: number;
  public readonly errors?: Record<string, string[]>;

  constructor(message: string, status?: number, errors?: Record<string, string[]>) {
    super(message);
    this.name = "ApiError";
    this.status = status;
    this.errors = errors;
  }
}

export const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
});

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (axios.isCancel(error)) {
      return Promise.reject(error);
    }

    if (axios.isAxiosError<ApiErrorResponse>(error)) {
      return Promise.reject(
        new ApiError(
          error.response?.data?.message ?? "Something went wrong",
          error.response?.status,
          error.response?.data?.errors,
        ),
      );
    }

    return Promise.reject(new ApiError("Unexpected error"));
  },
);

export function isRequestCanceled(error: unknown): boolean {
  return axios.isCancel(error);
}
