<?php

declare(strict_types=1);

namespace App\Dto;

abstract class Card
{
	public const TYPE_BASIC = 'Basic';
	public const TYPE_CLOZE = 'Cloze';

	protected string $type = self::TYPE_BASIC;

	public function __construct(
		private string         $front,
		private string         $back,
		private readonly array $tags = []
	) {
		// double new line
		$this->front = preg_replace('/\n{2,}/', "\n\n\n", $this->front);
		$this->front = preg_replace('/\n{2,}/', "\n\n\n", $this->front);
		// nl2br
		$this->front = nl2br($this->front);
		$this->back = nl2br($this->back);

		if ($this->hasPicture()) {
			printf("Card with back (%s) has picture\n", $this->back);
			$this->front = $this->replacePictureAsHtml($this->front);
			$this->front = $this->replaceSubdirInPicturePath($this->front);
		}
	}

	public function getFront(): string {
		return $this->front;
	}

	public function getBack(): string {
		return $this->back;
	}

	public function getTags(): array {
		return $this->tags;
	}

	public function getPicturesPaths(): array {
		// ![](Pasted_image_20250409151639.png)
		$pattern1 = '/!\[.*\]\((.*)\)/';
		// <img.....
		$pattern3 = '/<img src="(.*?)"\s*\/?>/';

		$result = [];
		if (preg_match($pattern1, $this->front, $matches)) {
			$result[] = $matches[1];
		}
		if (preg_match($pattern3, $this->front, $matches)) {
			$result[] = $matches[1];
		}

		return $result;
	}

	public function hasPicture(): bool {
		return count($this->getPicturesPaths()) > 0;
	}

	public function isValid(): bool {
		return !(empty($this->front) || empty($this->back));
	}

	public function getPictureUrl(): string|null {
		// get the path of the file and then check if the picture is at this path or in attachments subdir
		$pattern = '/!\[.*?\]\((.*?)\)/';
		preg_match($pattern, $this->front, $matches);
		if (isset($matches[1])) {
			$picturePath = $matches[1];
			// check if the picture is in attachments subdir
			if (str_contains($picturePath, 'attachments/')) {
				$picturePath = str_replace('attachments/', '', $picturePath);
			}

			return $picturePath;
		}

		return null;
	}

	private function replacePictureAsHtml(string $front): array|string|null {
		$pattern = '/!\[.*?\]\((.*?)\)/';
		//$replacement = '<img src="' . basename($this->getPictureUrl()) . '" />';
		$replacement = '<img src="$1" />';
		$front = preg_replace($pattern, $replacement, $front);

		return $front;
	}

	private function replaceSubdirInPicturePath(string $front): string {
		// <img src="attachments/Pasted_image_20250409151639.png">
		// to
		// <img src="Pasted_image_20250409151639.png">

		// if has ANY subdir
		$pattern = '/<img src="(.*?)\/(.*?)" \/>/';
		$replacement = '<img src="$2" />';

		return preg_replace($pattern, $replacement, $front);
	}

	public function getType(): string {
		return $this->type;
	}
}