<?php

namespace App\Helpers\Response;

class ResponseHelper
{
	public static function jsonResponse($data, $statusCode = 200)
	{
		http_response_code($statusCode);
		header('Content-Type: application/json');

		if (defined('JSON_PRESERVE_ZERO_FRACTION')) {
			$options = JSON_PRESERVE_ZERO_FRACTION;
		}

		echo json_encode($data, $options);
		exit;
	}
}