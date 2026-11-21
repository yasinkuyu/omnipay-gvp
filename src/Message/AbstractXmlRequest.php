<?php

namespace Omnipay\Gvp\Message;

/**
 * XML API (VPServlet, GVPSRequest). Bağlantı hatasında Omnipay'in NetworkException'ı fırlar: sonuç belirsizdir.
 *
 * HashData: SHA-512 büyük harf (OrderID + TerminalID + kart no (boş) + Amount + CurrencyCode + güvenlik verisi).
 * İade ve iptal iade kullanıcısıyla (PROVRFN) ve onun şifresiyle imzalanır.
 */
abstract class AbstractXmlRequest extends AbstractRequest
{
    /** Transaction/Type */
    abstract protected function type(): string;

    /** Transaction/Amount (kuruş) */
    abstract protected function amountField(): string;

    protected function isRefundUser(): bool
    {
        return false;
    }

    protected function extraTransaction(): array
    {
        return [];
    }

    public function getData(): array
    {
        $this->validate('merchantId', 'terminalId', 'transactionId');
        $user = $this->isRefundUser() ? (string) $this->getRefundUser() : (string) $this->getProvisionUser();
        $pass = $this->isRefundUser() ? (string) ($this->getRefundPassword() ?: $this->getPassword()) : (string) $this->getPassword();
        $orderId = (string) $this->getTransactionId();
        $terminal = (string) $this->getTerminalId();
        $amount = $this->amountField();
        $currency = $this->getCurrencyNumeric();

        return [
            'Mode' => $this->getTestMode() ? 'TEST' : 'PROD',
            'Version' => '512',
            'Terminal' => [
                'ProvUserID' => $user,
                'HashData' => strtoupper(hash('sha512', $orderId . $terminal . '' . $amount . $currency . self::securityData($pass, $terminal))),
                'UserID' => $user,
                'ID' => $terminal,
                'MerchantID' => (string) $this->getMerchantId(),
            ],
            'Customer' => ['IPAddress' => (string) ($this->getClientIp() ?: '127.0.0.1'), 'EmailAddress' => ''],
            'Order' => ['OrderID' => $orderId, 'GroupID' => ''],
            'Transaction' => [
                'Type' => $this->type(),
                'InstallmentCnt' => '',
                'Amount' => $amount,
                'CurrencyCode' => $currency,
                'CardholderPresentCode' => '0',
                'MotoInd' => 'N',
            ] + $this->extraTransaction(),
        ];
    }

    public function sendData($data): XmlResponse
    {
        $res = $this->httpClient->request('POST', $this->endpointFor('api'), ['Content-Type' => 'application/x-www-form-urlencoded'],
            http_build_query(['data' => self::toXml($data)]));

        return $this->response = new XmlResponse($this, XmlResponse::parse((string) $res->getBody()));
    }

    public static function toXml(array $data): string
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $add = function (\DOMNode $parent, array $data) use ($doc, &$add) {
            foreach ($data as $k => $v) {
                $el = $doc->createElement((string) $k);
                is_array($v) ? $add($el, $v) : $el->appendChild($doc->createTextNode((string) $v));
                $parent->appendChild($el);
            }
        };
        $root = $doc->createElement('GVPSRequest');
        $add($root, $data);
        $doc->appendChild($root);

        return (string) $doc->saveXML();
    }
}
