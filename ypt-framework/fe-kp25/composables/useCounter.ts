import { ref } from "vue";
import showToast from "~/helpers/toast";

export function useCounter() {
  const count = ref(0);

  const increment = () => {
    count.value++;
  };

  const decrement = () => {
    count.value--;
  };

  const reset = () => {
    count.value = 0;
    showToast("Counter reset", "success");
  };

  return {
    count,
    increment,
    decrement,
    reset,
  };
}
