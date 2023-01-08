<?php

namespace App\Objects\Ynab;

use Spatie\DataTransferObject\DataTransferObject;

class YnabOAuthTokenDTO extends DataTransferObject
{
	public string $access_token;
	public string $token_type;
	public int $expires_in;
	public string $refresh_token;
	public string $scope;
	public int $created_at;
}