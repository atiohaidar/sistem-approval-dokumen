import { ref } from 'vue';

/**
 * Error handling composable for consistent error management
 */
export const useErrorHandler = () => {
  const toast = ref<{
    show: boolean;
    message: string;
    type: 'error' | 'success' | 'warning' | 'info';
  }>({
    show: false,
    message: '',
    type: 'error',
  });

  /**
   * Handle API errors with user-friendly messages
   */
  const handleError = (error: any): string => {
    // Network errors
    if (!error.response) {
      return 'Network error. Please check your internet connection.';
    }

    const status = error.response?.status;
    const data = error.response?.data;

    // Handle different status codes
    switch (status) {
      case 400:
        return data?.message || 'Bad request. Please check your input.';
      case 401:
        return 'Session expired. Please log in again.';
      case 403:
        return 'You do not have permission to perform this action.';
      case 404:
        return 'The requested resource was not found.';
      case 422:
        // Validation errors
        if (data?.errors) {
          const firstError = Object.values(data.errors)[0] as string[];
          return firstError?.[0] || 'Validation error occurred.';
        }
        return data?.message || 'Validation error occurred.';
      case 429:
        return 'Too many requests. Please try again later.';
      case 500:
        return 'Server error. Please try again later.';
      case 503:
        return 'Service unavailable. Please try again later.';
      default:
        return data?.message || 'An unexpected error occurred.';
    }
  };

  /**
   * Show error toast notification
   */
  const showError = (error: any) => {
    const message = handleError(error);
    toast.value = {
      show: true,
      message,
      type: 'error',
    };

    // Auto-hide after 5 seconds
    setTimeout(() => {
      toast.value.show = false;
    }, 5000);
  };

  /**
   * Show success toast notification
   */
  const showSuccess = (message: string) => {
    toast.value = {
      show: true,
      message,
      type: 'success',
    };

    setTimeout(() => {
      toast.value.show = false;
    }, 3000);
  };

  /**
   * Show warning toast notification
   */
  const showWarning = (message: string) => {
    toast.value = {
      show: true,
      message,
      type: 'warning',
    };

    setTimeout(() => {
      toast.value.show = false;
    }, 4000);
  };

  /**
   * Show info toast notification
   */
  const showInfo = (message: string) => {
    toast.value = {
      show: true,
      message,
      type: 'info',
    };

    setTimeout(() => {
      toast.value.show = false;
    }, 3000);
  };

  /**
   * Clear toast notification
   */
  const clearToast = () => {
    toast.value.show = false;
  };

  return {
    toast,
    handleError,
    showError,
    showSuccess,
    showWarning,
    showInfo,
    clearToast,
  };
};
