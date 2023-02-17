export interface Product {
  id: number;
  name: string;
  available: number;
  price: string;
  vatRate: number;
  vat: string;
  count?: number;
  boughtAt?: string;
}
