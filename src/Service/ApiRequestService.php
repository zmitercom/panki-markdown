<?php

declare(strict_types=1);

namespace App\Service;

use App\Constant\Api;
use App\Exception\ConnectionException;

class ApiRequestService
{
	// curl localhost:8765 -X POST -d '{"action": "deckNames", "version": 6}'
	/**
	 * @throws ConnectionException
	 */
	public function do(array $post) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, Api::API_URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Accept: application/json',
		]);
		$response = curl_exec($ch);
		if (curl_errno($ch)) {
			throw new ConnectionException('Curl error: ' . curl_error($ch));
		}
		curl_close($ch);

		return json_decode($response, true);
	}
}