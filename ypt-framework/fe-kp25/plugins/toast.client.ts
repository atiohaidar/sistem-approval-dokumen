import Toast, { useToast, POSITION } from "vue-toastification";
import "vue-toastification/dist/index.css";

interface ShowToastParams {
  msg?: string;
  type?: "success" | "error" | "info" | "warning";
  time?: number;
  position?: keyof typeof POSITION;
  icon?: string | boolean;
}

export default defineNuxtPlugin((nuxtApp) => {
  nuxtApp.vueApp.use(Toast);

  const toast = useToast();

  nuxtApp.provide("showToast", (params: ShowToastParams) => {
    const type = params.type || "success";
    const message = params.msg || "default";
    const position = POSITION[params.position || "TOP_RIGHT"];

    toast[type](message, {
      timeout: params.time || 2000,
      position,
      icon: params.icon,
    });
  });
});
