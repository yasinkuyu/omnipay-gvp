# Omnipay: GVP (Garanti BBVA)

**Garanti BBVA sanal POS için [Omnipay](https://github.com/thephpleague/omnipay) sürücüsü**

[![Latest Stable Version](https://poser.pugx.org/yasinkuyu/omnipay-gvp/v/stable)](https://packagist.org/packages/yasinkuyu/omnipay-gvp)
[![Total Downloads](https://poser.pugx.org/yasinkuyu/omnipay-gvp/downloads)](https://packagist.org/packages/yasinkuyu/omnipay-gvp)
[![License](https://poser.pugx.org/yasinkuyu/omnipay-gvp/license)](https://packagist.org/packages/yasinkuyu/omnipay-gvp)

**Türkçe** | [English](README_EN.md)

## Bu paket ne yapar?

Mağazanızda Garanti BBVA sanal POS'unuzla kartla ödeme almanızı sağlar. Müşteri **Garanti'nin Ortak Ödeme
Sayfası'na** gider, kartını orada girer, 3D Secure doğrulamasını yapar ve sitenize geri döner. Kart bilgisi sizin
sunucunuzdan hiç geçmez.

```
Siteniz ──(imzalı form)──▶ Garanti Ortak Ödeme Sayfası ──(3D Secure)──▶ Siteniz (dönüş adresi)
```

**Özellikler**

| | |
|---|---|
| Ödeme | Ortak Ödeme Sayfası `3D_OOS_PAY` (varsayılan), `3D_PAY` |
| Taksit | 2-12 (bankanızla anlaştığınız kadar) |
| İade / iptal | `refund()` (tam ya da kısmi), `void()` (aynı gün, gün sonundan önce) |
| Sorgu | `fetchTransaction()` ile siparişin bankadaki durumu |
| Güvenlik | v512 (SHA-512) imza; dönüşte imza, terminal no, sipariş no ve tutar denetimi |

## Kurulum

```bash
composer require yasinkuyu/omnipay-gvp php-http/guzzle7-adapter
```

PHP 8.1 ya da üstü gerekir.

## Bankadan almanız gereken bilgiler

| Ayar | Nedir? | Nereden bulunur? |
|---|---|---|
| `merchantId` | Üye iş yeri numarası | Banka sanal POS başvurusu sonrası gönderilen bilgiler |
| `terminalId` | Terminal numarası | Aynı bilgiler |
| `password` | PROVAUT kullanıcısının şifresi | Sanal POS yönetim ekranı → Ayarlar → Şifre değiştirme (PROVAUT) |
| `storeKey` | 3D güvenlik anahtarı | Sanal POS yönetim ekranı → Ayarlar → 3D Secure güvenlik anahtarı. Kendiniz belirlersiniz |
| `refundPassword` | PROVRFN kullanıcısının şifresi (iade / iptal) | Aynı ekranda PROVRFN için. Boşsa `password` kullanılır |

`provisionUser` (varsayılan `PROVAUT`) ve `refundUser` (varsayılan `PROVRFN`) çoğu zaman değiştirilmez.

## Hızlı başlangıç

### 1. Gateway'i hazırlayın

```php
use Omnipay\Omnipay;

$gateway = Omnipay::create('Gvp');
$gateway->initialize([
    'merchantId'     => '7000679',
    'terminalId'     => '30691297',
    'password'       => '123qweASD/',
    'storeKey'       => '12345678',
    'refundPassword' => '123qweASD/',
    'testMode'       => true,   // canlıya geçerken false
]);
```

### 2. Ödemeyi başlatın (odeme.php)

```php
$response = $gateway->purchase([
    'amount'        => '149.90',
    'currency'      => 'TRY',
    'transactionId' => 'SIP-10045',                          // sizin sipariş numaranız, her denemede benzersiz
    'returnUrl'     => 'https://magaza.com/odeme-donus.php', // başarılı ve başarısız dönüş buraya
    'installment'   => 3,                                    // tek çekim için 1 ya da hiç yazmayın
    'clientIp'      => $_SERVER['REMOTE_ADDR'],
    'companyName'   => 'Mağazam',                            // ödeme sayfasında görünen mağaza adı
    'card'          => ['email' => 'ali@ornek.com'],         // kart numarası YOK
])->send();

$response->redirect(); // tarayıcıyı Garanti'nin ödeme sayfasına götüren formu basar ve gönderir
```

### 3. Dönüşü doğrulayın (odeme-donus.php)

```php
$result = $gateway->completePurchase([
    'transactionId' => 'SIP-10045', // KENDİ kaydınızdaki sipariş no
    'amount'        => '149.90',    // KENDİ kaydınızdaki tutar
])->send();

if (!$result->isVerified()) {
    // İmza tutmadı ya da sipariş no / tutar farklı: bu dönüşe güvenmeyin.
} elseif ($result->isSuccessful()) {
    // Ödeme alındı.
    $ref = $result->getTransactionReference(); // bankanın referans no (hostrefnum): iade / iptal için saklayın
    $result->getAuthCode();                    // onay kodu
    $result->getMaskedCard();                  // 42822090****8015
    $result->getInstallment();
} else {
    echo $result->getMessage();                // bankanın ret nedeni ya da 3D Secure hatası
}
```

> **Doğrulama nasıl çalışır?** Banka dönüşü imzalar: `hashparams` alanında sayılan değerler sırayla birleştirilir,
> sonuna `storeKey` eklenir ve SHA-1 alınır. Paket sonucu belirleyen alanların (sipariş no, sonuç kodu, yanıt, 3D
> durumu) imzanın içinde olmasını şart koşar; ayrıca terminal no, sipariş no ve tutarı karşılaştırır.

## İade, iptal, sorgu

```php
$p = ['transactionId' => 'SIP-10045', 'transactionReference' => $ref];

$gateway->void($p)->send();                                      // aynı gün: iptal (ekstrede görünmez)
$gateway->refund($p + ['amount' => '50.00'])->send();            // sonraki günler ya da kısmi: iade
$status = $gateway->fetchTransaction(['transactionId' => 'SIP-10045'])->send();
$status->getOrderInquiry()['Status'];                            // APPROVED …
```

Her yanıtta `isSuccessful()` ve `getMessage()` vardır. **`isReadable()` false dönerse** bankanın yanıtı okunamamıştır:
işlem gerçekleşmiş olabilir. Otomatik olarak yeniden denemeyin; sanal POS ekranından kontrol edin.

## Diğer ayarlar

| Ayar | Varsayılan | Açıklama |
|---|---|---|
| `securityLevel` | `3D_OOS_PAY` | `3D_PAY`: kart formu sizin sayfanızda, doğrudan bankaya POST edilir |
| `cancelUrl` | `returnUrl` | Başarısız ödemenin döneceği ayrı adres |
| `lang` | `tr` | Ödeme sayfasının dili: `tr` ya da `en` |
| `refreshTime` | bankanın varsayılanı | Sonuç ekranında kaç saniye bekleyip size dönsün |
| `endpoint` | | Farklı kurulum için `['gate' => '…/gt3dengine', 'api' => '…/VPServlet']` |

## Test ortamı

`testMode => true` iken istekler Garanti'nin test ortamına gider, para çekilmez.

| | |
|---|---|
| Üye iş yeri / terminal | `7000679` / `30691297` |
| Şifre (PROVAUT ve PROVRFN) | `123qweASD/` (sondaki `/` şifrenin parçası) |
| Store key | `12345678` |
| Test kartı | `4282 2090 0434 8015`, son kullanma 08/30, CVV `123` |

Bu bilgilerle ödeme uçtan uca onaylanır. Test ortamı iptal ve iadeyi "05" ile reddeder; istek biçimi ve kimlik
doğrulaması yine de denenebilir.

## Sık karşılaşılan durumlar

| Belirti | Neden |
|---|---|
| "Guvenlik Kodu hatali" | `password` ya da `storeKey` yanlış (test şifresinin sonunda `/` var) |
| "Kimlik doğrulama başarısız" (iade / sorgu) | PROVAUT / PROVRFN şifresi yanlış |
| "işlem güvenlik politikalarımız gereği engellenmiştir" | Bankanın güvenlik duvarı; otomatik test tarayıcıları (HeadlessChrome) engellenir |
| Dönüşte oturum düşüyor | Banka dönüşü siteler arası POST'tur: dönüş adresinde oturum çerezine güvenmeyin, sipariş no ile kaydı bulun |

## 2.x'ten geçiş

3.0, Omnipay v3 için baştan yazıldı:

- Kart numarasıyla doğrudan (3D'siz) satış kaldırıldı; ödeme Garanti'nin ödeme sayfasında alınır.
- Ayar adları: `merchantId`, `terminalId`, `password`, `storeKey`, `refundPassword` (`username` / `refundusername` yerine `provisionUser` / `refundUser`).
- `completePurchase()` dönüşü doğrular; iade ve iptal için `transactionReference` (hostrefnum) gerekir.
- Eski sürümü (2.x) kullanmaya devam etmek isterseniz: `composer require yasinkuyu/omnipay-gvp:^2.0`

## Testler

```bash
composer install
vendor/bin/phpunit
```

## Diğer Omnipay paketleri

Aynı geliştiricinin diğer Omnipay sürücüleri:

- **[Omnipay: NestPay (EST / Payten)](https://github.com/yasinkuyu/omnipay-nestpay)** — İş Bankası, Akbank, Halkbank, Ziraat, TEB, QNB, ING, Şekerbank, Anadolubank sanal POS'u; 3D Pay Hosting
- **[Omnipay: Paratika](https://github.com/yasinkuyu/omnipay-paratika)** — Paratika, VakıfPayS ve ZiraatPay ortak ödeme sayfası
- **[Omnipay: Paycell SDK](https://github.com/yasinkuyu/omnipay-paycell-sdk)** — Turkcell Paycell ödeme sayfası (Web SDK)
- **[Omnipay: Paycell API](https://github.com/yasinkuyu/omnipay-paycell)** — Turkcell Paycell ödeme API'si
- **[Omnipay: iyzico](https://github.com/yasinkuyu/omnipay-iyzico)** — iyzico ödeme formu
- **[Omnipay: PayTR](https://github.com/yasinkuyu/omnipay-paytr)** — PayTR sanal POS
- **[Omnipay: Posnet](https://github.com/yasinkuyu/omnipay-posnet)** — Yapı Kredi Posnet sanal POS'u

## Lisans

MIT — [Yasin Kuyu](https://github.com/yasinkuyu)
