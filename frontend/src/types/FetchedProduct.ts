export interface FetchedProduct {
  id: number;
  name: string;
  available: number;
  price: string;
  vat_rate: number;
  vat: string;
  count?: number;
  bought_at?: string;
}
