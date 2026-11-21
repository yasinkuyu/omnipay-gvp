<?php

namespace Omnipay\Gvp\Message;

/** Sipariş sorgusu (orderinq): transactionId (sipariş no). Sonuç XmlResponse::getOrderInquiry() */
class FetchTransactionRequest extends AbstractXmlRequest
{
    protected function type(): string { return 'orderinq'; }

    protected function amountField(): string { return '100'; }
}
