import type { Cart } from "@/types/Cart";

export interface Store {
  token: string;
  message: string;
  cart: Cart | null;
}
