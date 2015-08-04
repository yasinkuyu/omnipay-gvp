<?php

namespace Omnipay\Gvp\Message;

/**
 * Gvp Void Request
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-gvp
 */
class VoidRequest extends PurchaseRequest {

    public function getData() {

        $this->validate('amount', 'card');
        $this->getCard()->validate();
        $currency = $this->getCurrency();

        $data['Transaction'] = array(
            'Type' => 'void',
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
