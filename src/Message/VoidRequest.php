<?php

namespace Omnipay\Gvp\Message;

/** İptal (gün sonundan önce, tutarın tamamı): transactionId (sipariş no), transactionReference (hostrefnum) */
class VoidRequest extends AbstractXmlRequest
{
    protected function type(): string { return 'void'; }

    /** Banka iptalde sabit tutar ister */
    protected function amountField(): string { return '100'; }

    protected function isRefundUser(): bool { return true; }

    protected function extraTransaction(): array
    {
        return ['OriginalRetrefNum' => (string) $this->getTransactionReference()];
    }
}
