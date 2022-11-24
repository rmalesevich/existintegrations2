<?php

namespace App\Objects\Trakt;

use Spatie\DataTransferObject\DataTransferObject;

class TraktOAuthTokenDTO extends DataTransferObject
{
	public string $access_token;
	public string $token_type;
	public int $expires_in;
	public string $refresh_token;
	public string $scope;
	public int $created_at;
}