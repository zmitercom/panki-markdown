<?php

declare(strict_types=1);

namespace App\Dto;

final class ClozetCard extends Card
{


	private string|null $word;

	public function __construct(string $text, string|null $word = null, array $tags = []) {
		$front = $text;
		$back = '';
		$this->word = $word;
		parent::__construct($front, $back, $tags);

		$this->type = Card::TYPE_CLOZE;
	}

	public function getWord(): string|null {
		return $this->word;
	}


	public function getBack(): string {
		return  '';
		// Clozet cards does not have back, but we need to return all the mp3 files there
		return implode(" ", $this->getMp3Array());
	}

}