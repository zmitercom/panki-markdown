<?php

declare(strict_types=1);

namespace App\Dto;

final class ClozetCard extends Card
{
	public function __construct(string $text, array $tags = []) {
		$front = $text;
		$back = '';
		parent::__construct($front, $back, $tags);

		$this->type = Card::TYPE_CLOZE;
	}
}