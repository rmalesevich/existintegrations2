<?php

namespace App\Objects\Ynab;

use Spatie\DataTransferObject\DataTransferObject;

class YnabTransactionsDataDTO extends DataTransferObject
{
    /** @var \App\Objects\Ynab\YnabTransactionsDTO[]|null */
    public $transactions;

    /** @var int|null */
    public $server_knowledge;
}