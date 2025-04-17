<?php

declare(strict_types=1);

namespace App\Command\Card;

use App\Exception\ApiResponseException;
use App\Exception\ConnectionException;
use App\Service\CardService;
use App\Service\RequestService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'card:convert_simple_card', description: 'Hello PhpStorm')]
class ConvertSimpleCardCommand extends Command
{
	public function __construct(
		private readonly RequestService $requestService,
		private readonly CardService    $cardService
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->addArgument('file', InputArgument::REQUIRED,
			              'Path to the markdown file');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		// get file from args
		$file = $input->getArgument('file');
		if (!file_exists($file)) {
			$output->writeln('<error>File not found</error>');

			return Command::FAILURE;
		}

		// read file
		$contents = file_get_contents($file);
		if ($contents === false) {
			$output->writeln('<error>Failed to read file</error>');

			return Command::FAILURE;
		}

		// ## Card front 1
		//
		//Card back 1. All cards use markdown syntax.
		//
		//[#tree](tree.md)
		//
		//## Card front 2
		//
		//Card back 2.
		//
		//## B-tree complexity: access, insert, delete
		//
		//All: O(log n)
		//
		//[#complexity](complexity.md) [#tree](tree.md) [#tag]()
		//
		//## Card with additional front data
		//
		//Front data goes here
		//
		//%
		//
		//Back data goes here
		//
		//And here
		//
		//[#tag1]() [#tag2]()
		//
		//## Not allowed card

		$cards = $this->splitFileToCards($contents);

		$deckName = 'ObsidianDeck';

		foreach ($cards as $card) {
			$lines = explode("\n", $card);
			$front = '';
			$back = '';
			$tags = [];
			foreach ($lines as $line) {
				if (str_starts_with($line, '## ')) {
					$front = trim(substr($line, 3));
				}
				else if (str_starts_with($line, '%')) {
					continue;
				}
				else if (preg_match('/^\[#(.+?)\]/', $line, $matches)) {
					$tags[] = trim($matches[1]);
				}
				else {
					$back .= trim($line) . "\n";
				}
			}
			if (!empty($front) && !empty($back)) {
				try {
					$this->cardService->addCardToAnki($deckName, $front, $back, $tags);
				} catch (ApiResponseException $e) {
					$output->writeln('<error>API response error: ' . $e->getMessage() . '</error>');

					return Command::FAILURE;
				} catch (ConnectionException $e) {
					$output->writeln('<error>Connection error: ' . $e->getMessage() . '</error>');

					return Command::FAILURE;
				}
			}
		}

		return Command::SUCCESS;
	}

	private function splitFileToCards(string $contents): array {
		$cards = [];
		$lines = explode("\n", $contents);
		$currentCard = [];
		foreach ($lines as $line) {
			if (preg_match('/^## /', $line)) {
				if (!empty($currentCard)) {
					$cards[] = implode("\n", $currentCard);
					$currentCard = [];
				}
			}
			$currentCard[] = $line;
		}
		if (!empty($currentCard)) {
			$cards[] = implode("\n", $currentCard);
		}

		return $cards;
	}
}
