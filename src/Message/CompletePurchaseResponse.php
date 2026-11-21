<?php

namespace Omnipay\Gvp\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\NotificationInterface;
use Omnipay\Common\Message\RequestInterface;

/**
 * 3D dönüşünün sonucu. isSuccessful(): imza geçerli + response = Approved + procreturncode = 00 + mdstatus 1-4.
 * isVerified() false ise alanlara güvenilmez: sonuç kaydedilmemelidir.
 */
class CompletePurchaseResponse extends AbstractResponse implements NotificationInterface
{
    public function __construct(RequestInterface $request, $data, protected bool $verified)
    {
        parent::__construct($request, $data);
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function isSuccessful(): bool
    {
        return $this->verified && strcasecmp((string) ($this->data['response'] ?? ''), 'Approved') === 0
            && (string) ($this->data['procreturncode'] ?? '') === '00'
            && in_array((string) ($this->data['mdstatus'] ?? ''), ['1', '2', '3', '4'], true);
    }

    public function getTransactionStatus(): string
    {
        if (!$this->verified) {
            return self::STATUS_PENDING;
        }

        return $this->isSuccessful() ? self::STATUS_COMPLETED : self::STATUS_FAILED;
    }

    /** Sipariş no (oid) */
    public function getTransactionId(): ?string
    {
        $v = (string) (($this->data['oid'] ?? '') ?: ($this->data['orderid'] ?? ''));

        return $v !== '' ? $v : null;
    }

    /** Bankanın referans numarası (hostrefnum): iade / iptal için saklanır */
    public function getTransactionReference(): ?string
    {
        return ($this->data['hostrefnum'] ?? '') !== '' ? (string) $this->data['hostrefnum'] : null;
    }

    public function getCode(): ?string
    {
        return ($this->data['procreturncode'] ?? '') !== '' ? (string) $this->data['procreturncode'] : null;
    }

    public function getMessage(): ?string
    {
        if (!$this->verified) {
            return 'Invalid signature';
        }
        if ($this->isSuccessful()) {
            return null;
        }
        $md = (string) ($this->data['mdstatus'] ?? '');
        if ($md !== '' && !in_array($md, ['1', '2', '3', '4'], true)) {
            $m = trim((string) ($this->data['mderrormessage'] ?? ''));

            return $m !== '' ? $m : '3D Secure authentication failed';
        }
        $m = trim((string) (($this->data['errmsg'] ?? '') ?: ($this->data['hostmsg'] ?? '')));

        return $m !== '' ? $m : null;
    }

    public function getAmount(): ?string
    {
        return isset($this->data['txnamount']) && $this->data['txnamount'] !== '' ? number_format(((int) $this->data['txnamount']) / 100, 2, '.', '') : null;
    }

    public function getInstallment(): int
    {
        return max(1, (int) ($this->data['txninstallmentcount'] ?? 0));
    }

    public function getMaskedCard(): ?string
    {
        $v = (string) (($this->data['maskedpan'] ?? '') ?: ($this->data['cardmask'] ?? ''));

        return $v !== '' ? $v : null;
    }

    public function getAuthCode(): ?string
    {
        return ($this->data['authcode'] ?? '') !== '' ? (string) $this->data['authcode'] : null;
    }

    public function getHostReference(): ?string
    {
        return $this->getTransactionReference();
    }

    public function getMdStatus(): ?string
    {
        return isset($this->data['mdstatus']) ? (string) $this->data['mdstatus'] : null;
    }
}
