import type { Product } from "@/types/Product";

export interface Cart {
  products: Product[];
  subtotal: string;
  vat: string;
  total: string;
}
