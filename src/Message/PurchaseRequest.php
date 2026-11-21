<?php

namespace Omnipay\Gvp\Message;

/**
 * 3D formu (apiversion 512). Ağ isteği yapmaz: imzalı alanları döndürür, tarayıcı bunları bankaya POST eder.
 *
 * Parametreler: amount, currency, transactionId (orderid, sipariş no), returnUrl (successurl), cancelUrl (errorurl,
 * yoksa returnUrl), installment, clientIp, card (yalnız e-posta), companyName (bankanın sayfasında görünen mağaza adı),
 * lang (tr | en), refreshTime (sonuç ekranında bekleme, sn).
 *
 * İmza: SHA-512 büyük harf (terminalid + orderid + txnamount + txncurrencycode + successurl + errorurl + txntype +
 * txninstallmentcount + storeKey + güvenlik verisi).
 */
class PurchaseRequest extends AbstractRequest
{
    protected string $txnType = 'sales';

    public function getCompanyName() { return $this->getParameter('companyName'); }
    public function setCompanyName($value) { return $this->setParameter('companyName', $value); }

    public function getLang() { return $this->getParameter('lang'); }
    public function setLang($value) { return $this->setParameter('lang', $value); }

    public function getRefreshTime() { return $this->getParameter('refreshTime'); }
    public function setRefreshTime($value) { return $this->setParameter('refreshTime', $value); }

    public function getData(): array
    {
        $this->validate('merchantId', 'terminalId', 'provisionUser', 'password', 'storeKey', 'amount', 'transactionId', 'returnUrl');

        $data = [
            'mode' => $this->getTestMode() ? 'TEST' : 'PROD',
            'apiversion' => '512',
            'secure3dsecuritylevel' => (string) ($this->getSecurityLevel() ?: '3D_OOS_PAY'),
            'terminalprovuserid' => (string) $this->getProvisionUser(),
            'terminaluserid' => (string) $this->getProvisionUser(),
            'terminalmerchantid' => (string) $this->getMerchantId(),
            'terminalid' => (string) $this->getTerminalId(),
            'txntype' => $this->txnType,
            'txnamount' => $this->amountKurus(),
            'txncurrencycode' => $this->getCurrencyNumeric(),
            'txninstallmentcount' => $this->installmentValue(),
            'orderid' => (string) $this->getTransactionId(),
            'successurl' => (string) $this->getReturnUrl(),
            'errorurl' => (string) ($this->getCancelUrl() ?: $this->getReturnUrl()),
            'customeripaddress' => (string) ($this->getClientIp() ?: ''),
            'customeremailaddress' => $this->getCard() ? mb_substr((string) $this->getCard()->getEmail(), 0, 64) : '',
            'companyname' => mb_substr((string) ($this->getCompanyName() ?: ''), 0, 64),
            'lang' => (string) ($this->getLang() ?: 'tr'),
            'txntimestamp' => gmdate('Y-m-d\TH:i:s\Z'),
        ];
        if ($this->getRefreshTime() !== null && $this->getRefreshTime() !== '') {
            $data['refreshtime'] = (string) (int) $this->getRefreshTime();
        }
        $data['secure3dhash'] = strtoupper(hash('sha512', $data['terminalid'] . $data['orderid'] . $data['txnamount'] . $data['txncurrencycode']
            . $data['successurl'] . $data['errorurl'] . $data['txntype'] . $data['txninstallmentcount'] . $this->getStoreKey()
            . self::securityData((string) $this->getPassword(), $data['terminalid'])));

        return $data;
    }

    public function sendData($data): PurchaseResponse
    {
        return $this->response = new PurchaseResponse($this, $data, $this->endpointFor('gate'));
    }
}
