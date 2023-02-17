import type { FetchedProduct } from "@/types/FetchedProduct";

export interface FetchedCart {
  products: FetchedProduct[];
  subtotal: string;
  vat: string;
  total: string;
}
