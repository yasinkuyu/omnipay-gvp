<?php

namespace Omnipay\Gvp;

use Omnipay\Common\AbstractGateway;
use Omnipay\Gvp\Message\CompletePurchaseRequest;
use Omnipay\Gvp\Message\FetchTransactionRequest;
use Omnipay\Gvp\Message\PurchaseRequest;
use Omnipay\Gvp\Message\RefundRequest;
use Omnipay\Gvp\Message\VoidRequest;

/**
 * Garanti BBVA sanal POS (GVP).
 *
 * purchase()          3D formu (varsayılan 3D_OOS_PAY: bankanın ortak ödeme sayfası, kart bilgisi orada girilir).
 *                     Ağ isteği yapmaz; yanıt bankaya POST yönlendirmesidir.
 * completePurchase()  bankanın dönüşü: hash (hashparams + store key, SHA-1) doğrulanır, sonuç okunur.
 * refund() / void()   XML API (VPServlet) ile iade / iptal; fetchTransaction() sipariş sorgusu (orderinq).
 *
 * (c) Yasin Kuyu — https://github.com/yasinkuyu/omnipay-gvp
 */
class Gateway extends AbstractGateway
{
    public function getName(): string
    {
        return 'Gvp';
    }

    public function getDefaultParameters(): array
    {
        return [
            'merchantId' => '',
            'terminalId' => '',
            'provisionUser' => 'PROVAUT',
            'password' => '',
            'storeKey' => '',
            'refundUser' => 'PROVRFN',
            'refundPassword' => '',
            'securityLevel' => '3D_OOS_PAY',
            'testMode' => false,
        ];
    }

    /** Üye iş yeri numarası */
    public function getMerchantId() { return $this->getParameter('merchantId'); }
    public function setMerchantId($value): self { return $this->setParameter('merchantId', $value); }

    /** Terminal numarası */
    public function getTerminalId() { return $this->getParameter('terminalId'); }
    public function setTerminalId($value): self { return $this->setParameter('terminalId', $value); }

    /** Provizyon kullanıcısı (PROVAUT) */
    public function getProvisionUser() { return $this->getParameter('provisionUser'); }
    public function setProvisionUser($value): self { return $this->setParameter('provisionUser', $value); }

    /** Provizyon kullanıcısının şifresi */
    public function getPassword() { return $this->getParameter('password'); }
    public function setPassword($value): self { return $this->setParameter('password', $value); }

    /** 3D güvenlik anahtarı (store key) */
    public function getStoreKey() { return $this->getParameter('storeKey'); }
    public function setStoreKey($value): self { return $this->setParameter('storeKey', $value); }

    /** İade / iptal kullanıcısı (PROVRFN) */
    public function getRefundUser() { return $this->getParameter('refundUser'); }
    public function setRefundUser($value): self { return $this->setParameter('refundUser', $value); }

    /** İade / iptal kullanıcısının şifresi (boşsa provizyon şifresi) */
    public function getRefundPassword() { return $this->getParameter('refundPassword'); }
    public function setRefundPassword($value): self { return $this->setParameter('refundPassword', $value); }

    /** 3D_OOS_PAY | 3D_PAY | 3D_OOS_FULL | 3D */
    public function getSecurityLevel() { return $this->getParameter('securityLevel'); }
    public function setSecurityLevel($value): self { return $this->setParameter('securityLevel', $value); }

    /** Listede olmayan kurulum için adresler: ['gate' => …/gt3dengine, 'api' => …/VPServlet] */
    public function getEndpoint() { return $this->getParameter('endpoint'); }
    public function setEndpoint($value): self { return $this->setParameter('endpoint', $value); }

    public function purchase(array $options = []): PurchaseRequest
    {
        return $this->createRequest(PurchaseRequest::class, $options);
    }

    public function completePurchase(array $options = []): CompletePurchaseRequest
    {
        return $this->createRequest(CompletePurchaseRequest::class, $options);
    }

    public function acceptNotification(array $options = []): CompletePurchaseRequest
    {
        return $this->createRequest(CompletePurchaseRequest::class, $options);
    }

    public function refund(array $options = []): RefundRequest
    {
        return $this->createRequest(RefundRequest::class, $options);
    }

    public function void(array $options = []): VoidRequest
    {
        return $this->createRequest(VoidRequest::class, $options);
    }

    public function fetchTransaction(array $options = []): FetchTransactionRequest
    {
        return $this->createRequest(FetchTransactionRequest::class, $options);
    }
}
