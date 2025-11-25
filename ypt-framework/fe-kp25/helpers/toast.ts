import { useNuxtApp } from "#app";

export default function showToast(
  msg: string,
  type: "success" | "error" | "info" | "warning" = "success",
) {
  const nuxtApp = useNuxtApp();
  nuxtApp.$showToast({
    msg,
    type,
  });
}
