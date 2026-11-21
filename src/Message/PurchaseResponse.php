<?php

namespace Omnipay\Gvp\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\RequestInterface;

/** Bankanın 3D / ortak ödeme sayfasına POST yönlendirmesi */
class PurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    public function __construct(RequestInterface $request, $data, protected string $redirectUrl)
    {
        parent::__construct($request, $data);
    }

    public function isSuccessful(): bool
    {
        return false;
    }

    public function isRedirect(): bool
    {
        return true;
    }

    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }

    public function getRedirectMethod(): string
    {
        return 'POST';
    }

    public function getRedirectData(): array
    {
        return $this->data;
    }

    public function getTransactionId(): ?string
    {
        return $this->data['orderid'] ?? null;
    }
}
