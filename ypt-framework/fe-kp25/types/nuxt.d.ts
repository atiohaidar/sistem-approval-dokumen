import { NuxtApp } from "nuxt/app";

declare module "nuxt/app" {
  interface NuxtApp {
    $showToast: (options: { msg: string; type: string; icon?: string }) => void;
  }
}
