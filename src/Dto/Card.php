<?php

declare(strict_types=1);

namespace App\Dto;

abstract class Card
{
	public const TYPE_BASIC = 'Basic';
	public const TYPE_CLOZE = 'Cloze';

	public string|null $mp3 = null;

	protected string $type = self::TYPE_BASIC;

	public function __construct(
		private string         $front,
		private string         $back,
		private readonly array $tags = []
	) {
		// double new line
		$this->front = preg_replace('/\n{2,}/', "\n\n\n", $this->front);
		$this->front = preg_replace('/\n{2,}/', "\n\n\n", $this->front);

		// nl2br:works not very well.
//		$this->front = nl2br($this->front);
//		$this->back = nl2br($this->back);

		$this->back = $this->parseMp3($this->back);

		$this->front = $this->replaceMarkDownToHtml($this->front);

		// cover each line to <p> tag
		$this->front = preg_replace('/\n/', '</p><p>', $this->front);

		// cover everything with <div> tag
		$this->front = "<div class=\"card\"><p>{$this->front}</p></div>";

		if ($this->hasPicture()) {
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
		// ![[PolishCards-250418-16-1.png]]
		$pattern2 = '/!\[\[([^\]]+)\]\]/';
		// <img.....
		$pattern3 = '/<img src="(.*?)"\s*\/?>/';

		$result = [];
		if (preg_match($pattern1, $this->front, $matches)) {
			$result[] = $matches[1];
		}
		if (preg_match($pattern2, $this->front, $matches)) {
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
		switch ($this->type) {
			case self::TYPE_CLOZE:
				return !(empty($this->front));
			case self::TYPE_BASIC:
				return !(empty($this->front) || empty($this->back));
			default:
				throw new \InvalidArgumentException('Unknown card type');
		}
	}

	private function replacePictureAsHtml(string $front): array|string|null {
		$pattern = '/!\[.*?\]\((.*?)\)/';
//		$replacement = '[picture:$1]';
		$replacement = '<img src="$1" />';
		$front = preg_replace($pattern, $replacement, $front);

		// ![[PolishCards-250418-16-1.png]]
		$pattern2 = '/!\[\[([^\]]+)\]\]/';
		preg_match_all($pattern2, $front, $matches, PREG_SET_ORDER, 0);

		return preg_replace($pattern2, $replacement, $front);
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

	private function replaceMarkDownToHtml(array|string|null $front): array|string|null {
		$front = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $front);
		$front = preg_replace('/\_\_(.*?)\_\_/', '<strong>$1</strong>', $front);
		$front = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $front);
		$front = preg_replace('/\_(.*?)\_/', '<em>$1</em>', $front);
		$front = preg_replace('/~~(.*?)~~/', '<del>$1</del>', $front);
		$front = preg_replace('/`(.*?)`/', '<code>$1</code>', $front);

		return $front;
	}

	public function findMp3(): string|null {
		return $this->mp3;

		// find url with https://..... .mp3
		$pattern = '/https?:\/\/.*?\.mp3/';
		preg_match($pattern, $this->back, $matches);
		if (isset($matches[0])) {
			return $matches[0];
		}

		return null;
	}

	private function parseMp3(string $back): string {
		// find <audio src="https://..... .mp3">
		$pattern = '/<audio src="(.*?)"[^>]*>/';
		preg_match($pattern, $back, $matches);
		if (isset($matches[1])) {
			$this->mp3 = $matches[1];

			// remove <audio> tag
			return (string)preg_replace($pattern, '', $back);
		}

		// [audio:https://....audio.mp3]
		$pattern2 = '/\[audio:(.*?)\]/';
		preg_match($pattern2, $back, $matches);
		if (isset($matches[1])) {
			$this->mp3 = $matches[1];

			// remove [audio:...] tag
			return (string)preg_replace($pattern2, '', $back);
		}

		// find just https://..... .mp3
		$pattern3 = '/https?:\/\/.*?\.mp3/';
		preg_match($pattern3, $back, $matches);
		if (isset($matches[0])) {
			$this->mp3 = $matches[0];

			// remove https://..... .mp3
			return (string)preg_replace($pattern3, '', $back);
		}

		return $back;
	}
}