# Omnipay: GVP (Garanti BBVA)

**[Omnipay](https://github.com/thephpleague/omnipay) driver for the Garanti BBVA virtual POS**

[![Latest Stable Version](https://poser.pugx.org/yasinkuyu/omnipay-gvp/v/stable)](https://packagist.org/packages/yasinkuyu/omnipay-gvp)
[![Total Downloads](https://poser.pugx.org/yasinkuyu/omnipay-gvp/downloads)](https://packagist.org/packages/yasinkuyu/omnipay-gvp)
[![License](https://poser.pugx.org/yasinkuyu/omnipay-gvp/license)](https://packagist.org/packages/yasinkuyu/omnipay-gvp)

[Türkçe](README.md) | **English**

## What does this package do?

It lets your shop take card payments through your Garanti BBVA virtual POS. The customer goes to **Garanti's hosted
payment page (Ortak Ödeme Sayfası)**, enters the card there, completes 3D Secure and comes back to your site. Card
data never touches your server.

```
Your site ──(signed form)──▶ Garanti hosted payment page ──(3D Secure)──▶ Your site (return URL)
```

**Features**

| | |
|---|---|
| Payment | Hosted payment page `3D_OOS_PAY` (default), `3D_PAY` |
| Installments | 2-12 (whatever your bank agreement allows) |
| Refund / void | `refund()` (full or partial), `void()` (same day, before end-of-day) |
| Status | `fetchTransaction()`: the order's status at the bank |
| Security | v512 (SHA-512) signing; the return is checked for signature, terminal, order number and amount |

## Installation

```bash
composer require yasinkuyu/omnipay-gvp php-http/guzzle7-adapter
```

Requires PHP 8.1 or later.

## What you need from your bank

| Setting | What is it? | Where to find it |
|---|---|---|
| `merchantId` | Merchant number | Sent by the bank after your virtual POS application |
| `terminalId` | Terminal number | Same |
| `password` | Password of the PROVAUT user | Virtual POS admin → Settings → Change password (PROVAUT) |
| `storeKey` | 3D security key | Virtual POS admin → Settings → 3D Secure security key. You set it yourself |
| `refundPassword` | Password of the PROVRFN user (refund / void) | Same screen, for PROVRFN. Falls back to `password` |

`provisionUser` (default `PROVAUT`) and `refundUser` (default `PROVRFN`) rarely need changing.

## Quick start

### 1. Set up the gateway

```php
use Omnipay\Omnipay;

$gateway = Omnipay::create('Gvp');
$gateway->initialize([
    'merchantId'     => '7000679',
    'terminalId'     => '30691297',
    'password'       => '123qweASD/',
    'storeKey'       => '12345678',
    'refundPassword' => '123qweASD/',
    'testMode'       => true,   // false in production
]);
```

### 2. Start the payment (pay.php)

```php
$response = $gateway->purchase([
    'amount'        => '149.90',
    'currency'      => 'TRY',
    'transactionId' => 'ORD-10045',                       // your order number, unique per attempt
    'returnUrl'     => 'https://shop.com/pay-return.php',  // both success and failure come back here
    'installment'   => 3,                                 // 1 or omit for a single payment
    'clientIp'      => $_SERVER['REMOTE_ADDR'],
    'companyName'   => 'My Shop',                         // shop name shown on the payment page
    'card'          => ['email' => 'ali@example.com'],    // NO card number
])->send();

$response->redirect(); // prints a form that takes the browser to Garanti's payment page
```

### 3. Verify the return (pay-return.php)

```php
$result = $gateway->completePurchase([
    'transactionId' => 'ORD-10045', // the order number from YOUR records
    'amount'        => '149.90',    // the amount from YOUR records
])->send();

if (!$result->isVerified()) {
    // Bad signature, or order number / amount differ: don't trust this return.
} elseif ($result->isSuccessful()) {
    // Payment received.
    $ref = $result->getTransactionReference(); // bank reference (hostrefnum): keep it for refund / void
    $result->getAuthCode();
    $result->getMaskedCard();                  // 42822090****8015
    $result->getInstallment();
} else {
    echo $result->getMessage();                // bank's decline reason or 3D Secure error
}
```

> **How does verification work?** The bank signs the return: the values listed in `hashparams` are concatenated in
> order, `storeKey` is appended and SHA-1 is taken. The package requires the fields that decide the outcome (order
> number, result code, response, 3D status) to be inside the signature, and also compares terminal, order number and
> amount.

## Refund, void, status

```php
$p = ['transactionId' => 'ORD-10045', 'transactionReference' => $ref];

$gateway->void($p)->send();                                      // same day: void (never shows on the statement)
$gateway->refund($p + ['amount' => '50.00'])->send();            // later days or partial: refund
$status = $gateway->fetchTransaction(['transactionId' => 'ORD-10045'])->send();
$status->getOrderInquiry()['Status'];                            // APPROVED …
```

Every response has `isSuccessful()` and `getMessage()`. **If `isReadable()` returns false**, the bank's response could
not be read: the operation may have gone through. Do not retry automatically; check the virtual POS panel.

## Other settings

| Setting | Default | Description |
|---|---|---|
| `securityLevel` | `3D_OOS_PAY` | `3D_PAY`: the card form is on your page and posts straight to the bank |
| `cancelUrl` | `returnUrl` | Separate URL for failed payments |
| `lang` | `tr` | Language of the payment page: `tr` or `en` |
| `refreshTime` | bank default | Seconds the result screen waits before returning |
| `endpoint` | | Other installation: `['gate' => '…/gt3dengine', 'api' => '…/VPServlet']` |

## Test environment

With `testMode => true` requests go to Garanti's test environment; no money is charged.

| | |
|---|---|
| Merchant / terminal | `7000679` / `30691297` |
| Password (PROVAUT and PROVRFN) | `123qweASD/` (the trailing `/` is part of it) |
| Store key | `12345678` |
| Test card | `4282 2090 0434 8015`, expiry 08/30, CVV `123` |

Payments are approved end to end with these. The test environment declines void and refund with "05"; the request
format and authentication can still be tested.

## Troubleshooting

| Symptom | Cause |
|---|---|
| "Guvenlik Kodu hatali" (security code error) | Wrong `password` or `storeKey` (the test password ends with `/`) |
| "Kimlik doğrulama başarısız" (authentication failed) on refund / status | Wrong PROVAUT / PROVRFN password |
| "işlem güvenlik politikalarımız gereği engellenmiştir" (blocked by security policy) | The bank's firewall; automated test browsers (HeadlessChrome) are blocked |
| Session is lost on return | The bank's return is a cross-site POST: don't rely on the session cookie on the return URL; find the record by order number |

## Upgrading from 2.x

3.0 is a rewrite for Omnipay v3:

- Direct (non-3D) sales with a card number were removed; payment is taken on Garanti's payment page.
- Setting names: `merchantId`, `terminalId`, `password`, `storeKey`, `refundPassword` (`provisionUser` / `refundUser` replace `username` / `refundusername`).
- `completePurchase()` verifies the return; refund and void need `transactionReference` (hostrefnum).
- To keep using the old version (2.x): `composer require yasinkuyu/omnipay-gvp:^2.0`

## Tests

```bash
composer install
vendor/bin/phpunit
```

## License

MIT — [Yasin Kuyu](https://github.com/yasinkuyu)
