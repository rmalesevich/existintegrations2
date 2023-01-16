<?php

namespace App\Objects\Ynab;

use Spatie\DataTransferObject\DataTransferObject;

class YnabTransactionsDTO extends DataTransferObject
{
    /** @var string|null */
    public $id;

    /** @var string|null */
    public $date;

    /** @var int|null */
    public $amount;

    /** @var string|null */
    public $memo;

    /** @var string|null */
    public $cleared;

    /** @var bool|null */
    public $approved;

    /** @var string|null */
    public $flag_color;

    /** @var string|null */
    public $account_id;

    /** @var string|null */
    public $payee_id;

    /** @var string|null */
    public $category_id;

    /** @var string|null */
    public $transfer_account_id;

    /** @var string|null */
    public $transfer_transaction_id;

    /** @var string|null */
    public $matched_transaction_id;

    /** @var string|null */
    public $import_id;

    /** @var bool|null */
    public $deleted;

    /** @var string|null */
    public $account_name;

    /** @var string|null */
    public $payee_name;

    /** @var string|null */
    public $category_name;

    /** @var \App\Objects\Ynab\YnabSubtransactionsDTO[]|null */
    public $subtransactions;
}