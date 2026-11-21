<?php

namespace Omnipay\Gvp\Message;

/** İade: transactionId (sipariş no), transactionReference (hostrefnum), amount */
class RefundRequest extends AbstractXmlRequest
{
    protected function type(): string { return 'refund'; }

    protected function amountField(): string
    {
        $this->validate('amount');

        return $this->amountKurus();
    }

    protected function isRefundUser(): bool { return true; }

    protected function extraTransaction(): array
    {
        return ['OriginalRetrefNum' => (string) $this->getTransactionReference()];
    }
}
