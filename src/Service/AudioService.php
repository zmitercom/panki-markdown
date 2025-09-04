<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

class AudioService
{
	public function __construct(
		private readonly HttpClient $httpClient,
	) {
	}

	private const BASE_URL = 'https://api.voicerss.org/';

	private const READSPEAKER_TESTOWY_URL = "https://app-eu.readspeaker.com/cgi-bin/rsent?customerid=8804&lang=pl_pl&readid=testowy&url=https%3A%2F%2Fe-polish.eu%2Fsystem%2Fobj%2Fdictionary%2Fread%2F";

	public function downloadAudio(string $text) {
		$url = self::READSPEAKER_TESTOWY_URL . $text;
		$filename = $this->getFileName($text);

	}
}