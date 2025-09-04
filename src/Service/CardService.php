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
		private readonly KernelInterface $kernel,
		private readonly MediaService    $mediaService
	) {
	}

	/**
	 * @throws ApiResponseException
	 * @throws ConnectionException
	 * @throws PictureException
	 */
	public function addCardToAnki(string $deckName, Card $card): int {
		$front = $card->getFront();
		$back = $card->getBack();
		$tags = $card->getTags();

		if ($card->getType() === Card::TYPE_CLOZE) {
			$fields = [
				'Text' => $front,
				'Back Extra' => $back,
				'Word' => $card->getWord(),
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
						'allowDuplicate' => false,
					],
					'tags' => $tags,
				],
			],
		];

		if ($card->hasPicture()) {
			foreach ($card->getPicturesPaths() as $picture) {
				$this->mediaService->uploadFile(
					$this->makePictureUrl($picture)
				);
				/*
				$post['params']['note']['picture'] = [
					[
						'path' => $this->makePictureUrl($picture),
						'filename' => basename($picture),
						//'fields' => ['Front'],
						// if choose 'Front' then it will be added to the front side (automatically)
						// So if the image is already in the text, it should not be added again
						'fields' => ['Text'],
						// But empty does not work for Cloze cards, so we need to add it to
					],
				];
				*/
			}
		}

		if ($card->getMp3Array()) {
			foreach ($card->getMp3Array() as $mp3) {
				// if just filename, then we need to upload it
				if (!str_contains($mp3, 'http')) {
//					$this->mediaService->uploadFile(
//						$this->makePictureUrl($mp3)
//					);
					$post['params']['note']['audio'][] = [
						'path' => $this->makePictureUrl($mp3),
						'filename' => basename($mp3),
						'fields' => [
							'Back Extra',
						],
					];
				}
				else {
					// if url
					$post['params']['note']['audio'][] = [
						'url' => $mp3,
						'filename' => basename($mp3),
						'fields' => [
							'Back Extra',
						],
					];
				}
			}
		}

		/*
		$post['params']['note']['audio'] = [
			[
				'url' => "https://assets.languagepod101.com/dictionary/japanese/audiomp3.php?kanji=猫&kana=ねこ",
				'filename' => 'audio.mp3',
				'fields' => [
					'Back Extra',
				],
			],
		];
		*/

//		dd($post);

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

	public function findClozeWord(string $front): string|null {
		// find {{c1::word}} in the front text
		preg_match_all('/{{c\d+::(.*?)}}/', $front, $matches);
		if (isset($matches[1]) && count($matches[1]) > 0) {
			return $matches[1][0];
		}

		return null;
	}
}