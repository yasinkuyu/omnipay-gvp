<?php

namespace Omnipay\Gvp\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * GVPSResponse. isSuccessful(): Transaction/Response/Code = 00. isReadable() false ise yanıt okunamadı:
 * işlem bankada olmuş olabilir (iade / iptal için "belirsiz", otomatik tekrar denenmemeli).
 */
class XmlResponse extends AbstractResponse
{
    public static function parse(string $body): ?array
    {
        if (trim($body) === '' || stripos($body, '<!DOCTYPE') !== false || stripos($body, '<!ENTITY') !== false) {
            return null;
        }
        $prev = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body, \SimpleXMLElement::class, LIBXML_NONET);
        libxml_use_internal_errors($prev);

        return $xml === false ? null : json_decode((string) json_encode($xml), true);
    }

    private function tx(): array
    {
        return is_array($this->data['Transaction'] ?? null) ? $this->data['Transaction'] : [];
    }

    private function resp(): array
    {
        return is_array($this->tx()['Response'] ?? null) ? $this->tx()['Response'] : [];
    }

    private static function str($v): string
    {
        return is_array($v) ? '' : trim((string) $v);
    }

    public function isReadable(): bool
    {
        return is_array($this->data) && $this->resp() !== [];
    }

    public function isSuccessful(): bool
    {
        return self::str($this->resp()['Code'] ?? '') === '00';
    }

    public function getCode(): ?string
    {
        $c = self::str($this->resp()['ReasonCode'] ?? '') ?: self::str($this->resp()['Code'] ?? '');

        return $c !== '' ? $c : null;
    }

    public function getMessage(): ?string
    {
        if (!$this->isReadable()) {
            return 'Unreadable response';
        }
        if ($this->isSuccessful()) {
            return null;
        }
        $m = self::str($this->resp()['ErrorMsg'] ?? '') ?: self::str($this->resp()['SysErrMsg'] ?? '') ?: self::str($this->resp()['Message'] ?? '');

        return $m !== '' ? $m : null;
    }

    /** Bankanın referans numarası (RetrefNum) */
    public function getTransactionReference(): ?string
    {
        $v = self::str($this->tx()['RetrefNum'] ?? '');

        return $v !== '' ? $v : null;
    }

    public function getTransactionId(): ?string
    {
        $v = self::str($this->data['Order']['OrderID'] ?? '');

        return $v !== '' ? $v : null;
    }

    /** Sorgu (orderinq): Order/OrderInqResult alanları */
    public function getOrderInquiry(): array
    {
        $r = $this->data['Order']['OrderInqResult'] ?? null;

        return is_array($r) ? $r : [];
    }
}
