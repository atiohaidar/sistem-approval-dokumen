import axios, { AxiosError } from "axios";
import showToast from "~/helpers/toast";

export const createAxiosInstance = (baseURL: string | undefined) => {
  const instance = axios.create({ baseURL });

  instance.interceptors.request.use(
    (config) => {
      return config;
    },
    (error) => {
      Promise.reject(error).then(() =>
        console.error("Interceptor Error:", error),
      );
    },
  );

  return instance;
};

const HTTP_STATUS = {
  UNAUTHORIZED: 401,
  TOO_MANY_REQUESTS: 429,
};

const ERROR_MESSAGES = {
  UNAUTHORIZED: "Sesi telah berakhir",
  TOO_MANY_REQUESTS: "Terlalu banyak permintaan, silakan coba lagi nanti.",
  DEFAULT: "Terjadi Kesalahan!",
};

let isSessionErrorShown = false;

const handleApiError = (
  error: AxiosError<{ message: string; code: number }>,
) => {
  if (!error.response) {
    console.error("API Error:", error);
    return { status: false, message: ERROR_MESSAGES.DEFAULT };
  }

  const { status, data } = error.response;
  const serverMessage = data?.message;

  if (status === HTTP_STATUS.UNAUTHORIZED) {
    if (serverMessage === "Unauthorized" && !isSessionErrorShown) {
      isSessionErrorShown = true;
      showToast(ERROR_MESSAGES.UNAUTHORIZED, "error");

      setTimeout(() => {
        window.location.href = "/";
        localStorage.clear();
        sessionStorage.clear();
      }, 2000);
    }
    return { status: false, message: ERROR_MESSAGES.UNAUTHORIZED };
  }

  if (status === HTTP_STATUS.TOO_MANY_REQUESTS) {
    showToast(ERROR_MESSAGES.TOO_MANY_REQUESTS, "error");
    return { status: false, message: ERROR_MESSAGES.TOO_MANY_REQUESTS };
  }

  if (status >= 400 && status < 600) {
    const message = serverMessage || `${status}: ${ERROR_MESSAGES.DEFAULT}`;
    showToast(message, "error");
    return { status: false, message };
  }

  showToast(ERROR_MESSAGES.DEFAULT, "error");
  return { status: false, message: ERROR_MESSAGES.DEFAULT };
};

export const apiCall = async (
  method: "get" | "post" | "put" | "delete",
  instance: any,
  url: string,
  data?: any,
  responseType?: string,
): Promise<{
  status: boolean;
  data?: any;
  message?: string;
  fileName?: string;
}> => {
  try {
    const headers = {
      "Content-Type":
        typeof data === "string" ? "application/json" : "multipart/form-data",
    };

    const config: any = {
      headers,
    };

    if (responseType) {
      config.responseType = responseType;
    }

    const result =
      method === "get"
        ? await instance.get(`/${url}`, config)
        : await instance[method](`/${url}`, data, config);

    let fileName: string | undefined;

    if (responseType === "blob") {
      const contentDisposition = result.headers["content-disposition"];
      if (contentDisposition) {
        const match = contentDisposition.match(/filename="?([^"]+)"?/);
        if (match && match[1]) {
          fileName = match[1];
        }
      }
    }

    return { status: true, data: result.data, fileName };
  } catch (error) {
    return handleApiError(
      error as AxiosError<{ message: string; code: number }>,
    );
  }
};
