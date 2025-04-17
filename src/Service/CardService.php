<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ApiResponseException;
use App\Exception\ConnectionException;

class CardService
{
	public function __construct(
		private readonly RequestService $requestService,
	) {
	}

	/**
	 * @throws ApiResponseException
	 * @throws ConnectionException
	 */
	public function addClozeCardToAnki(string $deckName, string $text, array $tags = []): void {
		$post = [
			'action' => 'addNote',
			'version' => 6,
			'params' => [
				'note' => [
					'deckName' => $deckName,
					'modelName' => 'Cloze',
					'fields' => [
						'Text' => $text,
					],
					'options' => [
						'allowDuplicate' => false,
					],
					'tags' => $tags,
				],
			],
		];

		$response = $this->requestService->do($post);

		if (isset($response['error'])) {
			throw new ApiResponseException('AnkiConnect error: ' . $response['error']);
		}
	}

	/**
	 * @throws ApiResponseException
	 * @throws ConnectionException
	 */
	public function addCardToAnki(string $deckName, string $front, string $back, array $tags = []): void {
		$post = [
			'action' => 'addNote',
			'version' => 6,
			'params' => [
				'note' => [
					'deckName' => $deckName,
					'modelName' => 'Basic',
					'fields' => [
						'Front' => $front,
						'Back' => $back,
					],
					'options' => [
						'allowDuplicate' => false,
					],
					'tags' => $tags,
				],
			],
		];

		$response = $this->requestService->do($post);

		if (isset($response['error'])) {
			throw new ApiResponseException('AnkiConnect error: ' . $response['error']);
		}
	}
}