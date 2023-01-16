<?php

namespace App\Objects\Ynab;

use Spatie\DataTransferObject\DataTransferObject;

class YnabSubtransactionsDTO extends DataTransferObject
{
    /** @var string|null */
    public $id;

    /** @var string|null */
    public $transaction_id;

    /** @var int|null */
    public $amount;

    /** @var string|null */
    public $memo;

    /** @var string|null */
    public $payee_id;

    /** @var string|null */
    public $payee_name;

    /** @var string|null */
    public $category_id;

    /** @var string|null */
    public $category_name;

    /** @var string|null */
    public $transfer_account_id;

    /** @var string|null */
    public $transfer_transaction_id;

    /** @var bool|null */
    public $deleted;
}