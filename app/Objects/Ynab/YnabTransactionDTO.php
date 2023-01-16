<?php

namespace App\Objects\Ynab;

use Spatie\DataTransferObject\DataTransferObject;

class YnabTransactionDTO extends DataTransferObject
{
    /** @var \App\Objects\Ynab\YnabTransactionsDataDTO|null */
    public $data;

    /** @var \App\Objects\Ynab\YnabErrorDTO|null */
    public $error;
}