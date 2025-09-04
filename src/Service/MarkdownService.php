<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\BasicCard;
use App\Dto\Card;
use App\Dto\ClozetCard;

class MarkdownService
{
	public function __construct(
		private readonly CardService $cardService,
	) {
	}

	/**
	 * @param string $contents
	 *
	 * @return Card[]
	 */
	public function splitFileToCards(string $contents): array {
		$cards = [];
		$chunks = preg_split('/^##[##]?\s*/m', $contents);

		foreach ($chunks as $chunk) {
			if (empty(trim($chunk))) {
				continue;
			}
			//echo "CHUNK:\n" . $chunk . "\n----\n";
			$chunk = trim($chunk);

			if ($this->isChunkCloze($chunk)) {
				$parsed = $this->parseClozeCard($chunk);
				$front = $parsed['front'];
				$tags = $parsed['tags'];
				$word = $this->cardService->findClozeWord($front);
				$cardElement = new ClozetCard($front, $word, $tags);
			}
			else {
				$parsed = $this->parseBasicCard($chunk);
				$front = $parsed['front'];
				$back = $parsed['back'];
				$tags = $parsed['tags'];
//				if (!empty($front) && !empty($back)) {
				$cardElement = new BasicCard($front, $back, $tags);
//				}
			}
			$cards[] = $cardElement;
		}

		return $cards;
	}

	private function parseBasicCard(string $chunk): array {
		$lines = explode("\n", $chunk);
		$front = '';
		$back = '';
		$tags = [];
		$collectingFront = true;

		foreach ($lines as $line) {
			$line = trim($line);
			if ($line === '' || $line === '%') {
				continue;
			}

			if (preg_match_all('/\[#([^\]]+)\]/', $line, $matches)) {
				foreach ($matches[1] as $tag) {
					$tags[] = trim($tag);
				}
				continue;
			}

			if (str_starts_with($line, '==')) {
				$collectingFront = false;
				$back .= trim(substr($line, 2)) . "\n";
				continue;
			}

			if ($collectingFront) {
				$front .= $line . "\n";
			}
			else {
				$back .= $line . "\n";
			}
		}

		$front = trim($front);
		$back = trim($back);

		return [
			'front' => $front,
			'back' => $back,
			'tags' => $tags,
		];
	}

	private function isChunkCloze(string $chunk): bool {
		return str_contains($chunk, '{{');
	}

	private function parseClozeCard(string $chunk): array {
		$lines = explode("\n", $chunk);
		$front = '';
//		$back = '';
		$tags = [];
//		$collectingFront = true;

		foreach ($lines as $line) {
			$line = trim($line);
			if ($line === '' || $line === '%') {
				continue;
			}

			if (preg_match_all('/\[#([^\]]+)\]/', $line, $matches)) {
				foreach ($matches[1] as $tag) {
					$tags[] = trim($tag);
				}
				continue;
			}

//			if (str_starts_with($line, '==')) {
//				$collectingFront = false;
//				$back .= trim(substr($line, 2)) . "\n";
//				continue;
//			}

//			if ($collectingFront) {
			$front .= $line . "\n";
//			}
//			else {
			//$back .= $line . "\n";
//			}
		}

		return [
			'front' => trim($front),
			//'back' => trim($back),
			'tags' => $tags,
		];
	}
}