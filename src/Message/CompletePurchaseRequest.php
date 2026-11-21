<?php

namespace Omnipay\Gvp\Message;

/**
 * Bankanın dönüşü (successurl / errorurl POST'u). Ağ isteği yapmaz; imzayı doğrular.
 *
 * İmza: base64(SHA-1(hashparams'ta sayılan alanların değerleri sırayla + storeKey)). Sipariş no, sonuç kodu, yanıt ve
 * 3D durumu imzalı alanlar arasında olmalıdır; değilse dönüş reddedilir. clientid terminal no ile, verilmişse oid
 * transactionId ile ve txnamount amount ile karşılaştırılır.
 */
class CompletePurchaseRequest extends AbstractRequest
{
    /** İmzada bulunması zorunlu alanlar */
    private const MUST_SIGN = ['oid', 'procreturncode', 'response', 'mdstatus'];

    public function getFields() { return $this->getParameter('fields'); }
    public function setFields($value) { return $this->setParameter('fields', $value); }

    public function getData(): array
    {
        $this->validate('terminalId', 'storeKey');

        return is_array($this->getFields()) ? $this->getFields() : self::parseBody($this->httpRequest);
    }

    public function sendData($data): CompletePurchaseResponse
    {
        $data = array_change_key_case($data, CASE_LOWER);

        return $this->response = new CompletePurchaseResponse($this, $data, $this->verify($data));
    }

    private function verify(array $d): bool
    {
        $given = (string) ($d['hash'] ?? '');
        $params = array_values(array_filter(explode(':', strtolower((string) ($d['hashparams'] ?? ''))), fn ($p) => $p !== ''));
        if ($given === '' || !$params || array_diff(self::MUST_SIGN, $params)) {
            return false;
        }
        $vals = '';
        foreach ($params as $p) {
            $vals .= (string) ($d[$p] ?? '');
        }
        if (!hash_equals(base64_encode(sha1($vals . $this->getStoreKey(), true)), $given)) {
            return false;
        }
        if ((string) ($d['clientid'] ?? $d['terminalid'] ?? '') !== (string) $this->getTerminalId()) {
            return false;
        }
        if ($this->getTransactionId() !== null && (string) ($d['oid'] ?? '') !== (string) $this->getTransactionId()) {
            return false;
        }
        if ($this->getParameter('amount') !== null && isset($d['txnamount']) && (string) $d['txnamount'] !== $this->amountKurus()) {
            return false;
        }

        return true;
    }

    /**
     * Alanlar istek gövdesinden okunur (aynı ad iki kez gelebilir, ilk değer alınır). Gövde boşsa PHP'nin ayrıştırdığı.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public static function parseBody($request): array
    {
        $body = (string) $request->getContent();
        if ($body === '' || !str_contains($body, '=')) {
            return $request->request->all();
        }
        $out = [];
        foreach (explode('&', $body) as $pair) {
            if ($pair === '') {
                continue;
            }
            [$k, $v] = array_pad(explode('=', $pair, 2), 2, '');
            $k = urldecode($k);
            if (!array_key_exists($k, $out)) {
                $out[$k] = urldecode($v);
            }
        }

        return $out;
    }
}
