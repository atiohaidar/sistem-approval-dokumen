/**
 * HTTP client composable with retry logic and timeout handling
 */
import axios, { AxiosError, AxiosRequestConfig } from 'axios';

export const useHttpClient = () => {
  const config = useRuntimeConfig();
  const { showError } = useErrorHandler();

  const defaultConfig: AxiosRequestConfig = {
    baseURL: config.public.apiBase,
    timeout: 30000, // 30 seconds
    withCredentials: true,
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
  };

  /**
   * Retry request with exponential backoff
   */
  const retryRequest = async (
    requestFn: () => Promise<any>,
    maxRetries = 3,
    delay = 1000
  ): Promise<any> => {
    for (let i = 0; i < maxRetries; i++) {
      try {
        return await requestFn();
      } catch (error: any) {
        const isLastRetry = i === maxRetries - 1;
        const isRetryableError =
          error.code === 'ECONNABORTED' ||
          error.code === 'ERR_NETWORK' ||
          error.response?.status >= 500;

        if (isLastRetry || !isRetryableError) {
          throw error;
        }

        // Exponential backoff
        await new Promise((resolve) => setTimeout(resolve, delay * Math.pow(2, i)));
      }
    }
  };

  /**
   * GET request with retry
   */
  const get = async <T = any>(url: string, config?: AxiosRequestConfig): Promise<T> => {
    try {
      return await retryRequest(async () => {
        const response = await axios.get<T>(url, { ...defaultConfig, ...config });
        return response.data;
      });
    } catch (error) {
      showError(error);
      throw error;
    }
  };

  /**
   * POST request (no retry for mutations to avoid duplicate operations)
   */
  const post = async <T = any>(
    url: string,
    data?: any,
    config?: AxiosRequestConfig
  ): Promise<T> => {
    try {
      const response = await axios.post<T>(url, data, { ...defaultConfig, ...config });
      return response.data;
    } catch (error) {
      showError(error);
      throw error;
    }
  };

  /**
   * PUT request
   */
  const put = async <T = any>(
    url: string,
    data?: any,
    config?: AxiosRequestConfig
  ): Promise<T> => {
    try {
      const response = await axios.put<T>(url, data, { ...defaultConfig, ...config });
      return response.data;
    } catch (error) {
      showError(error);
      throw error;
    }
  };

  /**
   * DELETE request
   */
  const del = async <T = any>(url: string, config?: AxiosRequestConfig): Promise<T> => {
    try {
      const response = await axios.delete<T>(url, { ...defaultConfig, ...config });
      return response.data;
    } catch (error) {
      showError(error);
      throw error;
    }
  };

  /**
   * Upload file with progress
   */
  const upload = async <T = any>(
    url: string,
    formData: FormData,
    onProgress?: (progress: number) => void
  ): Promise<T> => {
    try {
      const response = await axios.post<T>(url, formData, {
        ...defaultConfig,
        headers: {
          ...defaultConfig.headers,
          'Content-Type': 'multipart/form-data',
        },
        onUploadProgress: (progressEvent) => {
          if (onProgress && progressEvent.total) {
            const percentCompleted = Math.round(
              (progressEvent.loaded * 100) / progressEvent.total
            );
            onProgress(percentCompleted);
          }
        },
      });
      return response.data;
    } catch (error) {
      showError(error);
      throw error;
    }
  };

  /**
   * Download file
   */
  const download = async (url: string, filename: string): Promise<void> => {
    try {
      const response = await axios.get(url, {
        ...defaultConfig,
        responseType: 'blob',
      });

      const blob = new Blob([response.data]);
      const link = document.createElement('a');
      link.href = window.URL.createObjectURL(blob);
      link.download = filename;
      link.click();
      window.URL.revokeObjectURL(link.href);
    } catch (error) {
      showError(error);
      throw error;
    }
  };

  return {
    get,
    post,
    put,
    delete: del,
    upload,
    download,
  };
};
