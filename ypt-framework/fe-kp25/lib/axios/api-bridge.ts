import { apiCall, createAxiosInstance } from "~/lib/axios/base-api-bridge";

export const getBlob = (url: string) => {
  const axiosInstance = createAxiosInstance(getBaseApiUrl());
  return apiCall("get", axiosInstance, url, undefined, "blob");
};

export const get = (url: string) => {
  const axiosInstance = createAxiosInstance(getBaseApiUrl());
  return apiCall("get", axiosInstance, url);
};

export const post = (url: string, data: string | FormData) => {
  const axiosInstance = createAxiosInstance(getBaseApiUrl());
  return apiCall("post", axiosInstance, url, data, undefined);
};

export const put = (url: string, data: string | FormData) => {
  const axiosInstance = createAxiosInstance(getBaseApiUrl());
  return apiCall("put", axiosInstance, url, data, undefined);
};

export const drop = (url: string) => {
  const axiosInstance = createAxiosInstance(getBaseApiUrl());
  return apiCall("delete", axiosInstance, url);
};

// Helper internal
function getBaseApiUrl(): string | undefined {
  const config = useRuntimeConfig();
  return config.public.baseApiUrl;
}
