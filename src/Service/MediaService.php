<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ConnectionException;
use App\Exception\PictureException;

readonly class MediaService
{
	public function __construct(
		private RequestService $requestService
	) {
	}

	/**
	 * Returns the basename of the file which is uploaded to Anki.
	 *
	 * @param string $absFilePath
	 *
	 * @return string
	 * @throws ConnectionException
	 * @throws PictureException
	 */
	public function uploadFile(string $absFilePath): string {
		$post = [
			'action' => 'storeMediaFile',
			'version' => 6,
			'params' => [
				'filename' => basename($absFilePath),
				'data' => base64_encode(file_get_contents($absFilePath)),
			],
		];

		$response = $this->requestService->do($post);
		if (isset($response['error'])) {
			throw new PictureException('Error uploading file: ' . $response['error']);
		}

		return $response['result'];
	}
}