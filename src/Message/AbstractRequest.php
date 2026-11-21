<?php

namespace Omnipay\Gvp\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest as BaseAbstractRequest;

/**
 * Ortak parametreler, adresler, güvenlik verisi.
 */
abstract class AbstractRequest extends BaseAbstractRequest
{
    public const TEST_GATE = 'https://sanalposprovtest.garantibbva.com.tr/servlet/gt3dengine';
    public const TEST_API = 'https://sanalposprovtest.garantibbva.com.tr/VPServlet';
    public const LIVE_GATE = 'https://sanalposprov.garanti.com.tr/servlet/gt3dengine';
    public const LIVE_API = 'https://sanalposprov.garanti.com.tr/VPServlet';

    public const CURRENCIES = ['TRY' => '949', 'TL' => '949', 'USD' => '840', 'EUR' => '978', 'GBP' => '826', 'JPY' => '392'];

    public function getMerchantId() { return $this->getParameter('merchantId'); }
    public function setMerchantId($value) { return $this->setParameter('merchantId', $value); }

    public function getTerminalId() { return $this->getParameter('terminalId'); }
    public function setTerminalId($value) { return $this->setParameter('terminalId', $value); }

    public function getProvisionUser() { return $this->getParameter('provisionUser'); }
    public function setProvisionUser($value) { return $this->setParameter('provisionUser', $value); }

    public function getPassword() { return $this->getParameter('password'); }
    public function setPassword($value) { return $this->setParameter('password', $value); }

    public function getStoreKey() { return $this->getParameter('storeKey'); }
    public function setStoreKey($value) { return $this->setParameter('storeKey', $value); }

    public function getRefundUser() { return $this->getParameter('refundUser'); }
    public function setRefundUser($value) { return $this->setParameter('refundUser', $value); }

    public function getRefundPassword() { return $this->getParameter('refundPassword'); }
    public function setRefundPassword($value) { return $this->setParameter('refundPassword', $value); }

    public function getSecurityLevel() { return $this->getParameter('securityLevel'); }
    public function setSecurityLevel($value) { return $this->setParameter('securityLevel', $value); }

    public function getEndpoint() { return $this->getParameter('endpoint'); }
    public function setEndpoint($value) { return $this->setParameter('endpoint', $value); }

    /** Taksit sayısı (1 ya da boş = peşin) */
    public function getInstallment() { return $this->getParameter('installment'); }
    public function setInstallment($value) { return $this->setParameter('installment', $value); }

    protected function endpointFor(string $kind): string
    {
        $custom = $this->getEndpoint();
        if (is_array($custom) && !empty($custom[$kind])) {
            $url = (string) $custom[$kind];
            if (!str_starts_with(strtolower($url), 'https://') || !filter_var($url, FILTER_VALIDATE_URL)) {
                throw new InvalidRequestException('Gvp endpoint must be an https URL.');
            }

            return $url;
        }
        if ($kind === 'gate') {
            return $this->getTestMode() ? self::TEST_GATE : self::LIVE_GATE;
        }

        return $this->getTestMode() ? self::TEST_API : self::LIVE_API;
    }

    public function getCurrencyNumeric(): string
    {
        $c = strtoupper((string) ($this->getCurrency() ?: 'TRY'));
        if (!isset(self::CURRENCIES[$c])) {
            throw new InvalidRequestException('Unsupported currency: ' . $c);
        }

        return self::CURRENCIES[$c];
    }

    /** Tutar kuruş olarak (149.90 → 14990) */
    protected function amountKurus(): string
    {
        return (string) $this->getAmountInteger();
    }

    /** "" ya da 2-12 */
    protected function installmentValue(): string
    {
        $n = (int) $this->getInstallment();

        return $n > 1 ? (string) $n : '';
    }

    /** Güvenlik verisi: SHA-1 büyük harf (şifre + 9 haneye sıfırla tamamlanmış terminal no) */
    public static function securityData(string $password, string $terminalId): string
    {
        return strtoupper(sha1($password . str_pad($terminalId, 9, '0', STR_PAD_LEFT)));
    }
}
