<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Card;
use App\Exception\ApiResponseException;
use App\Exception\ConnectionException;
use App\Exception\PictureException;
use Symfony\Component\HttpKernel\KernelInterface;

class CardService
{
	private string|null $markdownFileAbsPath = null;

	public function __construct(
		private readonly RequestService  $requestService,
		private readonly KernelInterface $kernel
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
	public function addCardToAnki(string $deckName, Card $card): int {
		$front = $card->getFront();
		$back = $card->getBack();
		$tags = $card->getTags();

		if ($card->getType() === Card::TYPE_CLOZE) {
			$fields = [
				'Text' => $front,
				'Back Extra' => $back,
			];
		}
		else {
			$fields = [
				'Front' => $front,
				'Back' => $back,
			];
		}

		$post = [
			'action' => 'addNote',
			'version' => 6,
			'params' => [
				'note' => [
					'deckName' => $deckName,
					'modelName' => $card->getType(),
					'fields' => $fields,
					'options' => [
						'allowDuplicate' => true,
					],
					'tags' => $tags,
				],
			],
		];

		if ($card->hasPicture()) {
			foreach ($card->getPicturesPaths() as $picture) {
				$post['params']['note']['picture'] = [
					[
						'path' => $this->makePictureUrl($picture),
						'filename' => basename($picture),
						//'fields' => ['Front'],
						// if choose 'Front' then it will be added to the front side (automatically)
						// So if the image is already in the text, it should not be added again
						'fields' => [],
					],
				];
			}
		}

		$response = $this->requestService->do($post);

		if (isset($response['error'])) {
			throw new ApiResponseException('AnkiConnect error: ' . $response['error']);
		}

		return (int)($response['result'] ?? -1);
	}

	/**
	 * @throws PictureException
	 */
	private function makePictureUrl(string $picturePath): string {
		$dir = dirname($this->markdownFileAbsPath);

		// direct path
		if (file_exists($dir . '/' . $picturePath)) {
			return $dir . '/' . $picturePath;
		}

		// attachments path
		if (file_exists($dir . '/attachments/' . $picturePath)) {
			return $dir . '/attachments/' . $picturePath;
		}

		throw new PictureException(
			sprintf("Picture %s not found in: \n %s \n %s",
			        $picturePath,
			        $dir . '/' . $picturePath,
			        $dir . '/attachments/' . $picturePath
			)
		);
	}

	public function setMarkdownFilePath(string $file): void {
		// convert relative path to absolute path
		if (!str_starts_with($file, '/')) {
			$file = $this->kernel->getProjectDir() . '/' . $file;
		}
		$this->markdownFileAbsPath = $file;
	}
}