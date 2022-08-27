<?php

namespace App\Objects\Exist;

use Spatie\DataTransferObject\DataTransferObject;

class ExistOAuthTokenDTO extends DataTransferObject
{
	public string $access_token;
	public string $token_type;
	public int $expires_in;
	public string $refresh_token;
	public string $scope;
}