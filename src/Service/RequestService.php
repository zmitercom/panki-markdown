<?php

declare(strict_types=1);

namespace App\Service;

use App\Constant\Api;

class RequestService
{
	// curl localhost:8765 -X POST -d '{"action": "deckNames", "version": 6}'
	/**
	 */
	public function do(array $post): array {
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
			return [
				'error' => 'Curl error: ' . curl_error($ch),
			];
		}
		curl_close($ch);

		return (array)json_decode($response, true);
	}
}