# Değişiklikler

## 3.0.0

- Omnipay v3 (`omnipay/common` ^3.3), PHP 8.1+
- Garanti Ortak Ödeme Sayfası (`3D_OOS_PAY`, varsayılan) ve `3D_PAY`; kart bilgisi sunucudan geçmez
- v512 (SHA-512) imza; dönüşte `hash` / `hashparams` doğrulaması (sonuç alanları imzada olmalı), terminal, sipariş no ve tutar denetimi
- `completePurchase()`, `acceptNotification()`, `fetchTransaction()` (orderinq)
- İade ve iptal PROVRFN kullanıcısıyla, banka referansı (hostrefnum) üzerinden
- Güncel adresler (sanalposprovtest.garantibbva.com.tr); Garanti test ortamında uçtan uca onaylı ödemeyle doğrulandı
- Kart numarasıyla doğrudan (3D'siz) satış kaldırıldı
