<?php

namespace Omnipay\Gvp\Message;

/**
 * Gvp Complete Capture Request
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-gvp
 */
class CaptureRequest extends PurchaseRequest {

    public function getData() {

        $this->validate('card');
        $this->getCard()->validate();
        $currency = $this->getCurrency();

        $data['Transaction'] = array(
            'Type' => 'sales',
            'InstallmentCnt' => $this->getInstallment(),
            'Amount' => $this->getAmountInteger(),
            'CurrencyCode' => $this->currencies[$currency],
            'CardholderPresentCode' => "0",
            'MotoInd' => "H",
            'Description' => "",
            'OriginalRetrefNum' => $this->getTransactionId(),
            'CepBank' => array(
                'GSMNumber' => $this->getCard()->getBillingPhone(),
                'CepBank' => ""
            ),
            'PaymentType' => "K" // K->Kredi Kartı, D->Debit Kart, V->Vadesiz Hesap
        );

        return $data;
    }

}
