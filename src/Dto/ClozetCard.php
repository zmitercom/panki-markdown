<?php

declare(strict_types=1);

namespace App\Dto;

final class ClozetCard extends Card
{
	public function __construct(string $text, string $backExtra, array $tags = []) {
		$front = $text;
		$back = $backExtra;
		parent::__construct($front, $back, $tags);

		$this->type = Card::TYPE_CLOZE;
	}
}