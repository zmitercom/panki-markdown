<?php

declare(strict_types=1);

namespace App\Command\Card;

use App\Dto\BasicCard;
use App\Dto\Card;
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

		$this->cardService->setMarkdownFilePath($file);

		// read file
		$contents = file_get_contents($file);
		if ($contents === false) {
			$output->writeln('<error>Failed to read file</error>');

			return Command::FAILURE;
		}

		$cards = $this->splitFileToCards($contents);
//		dd($cards);

		$deckName = 'ObsidianDeck';

		foreach ($cards as $card) {
			$front = $card->getFront();
			if ($card->isValid() === false) {
				$output->writeln('<error>Invalid card: ' . $front . '</error>');
				continue;
			}

			try {
				$id = $this->cardService->addCardToAnki($deckName, $card);
				$output->writeln('<info>Card added: ' . $front . ' (ID: ' . $id . ')</info>');
			} catch (ApiResponseException $e) {
				$output->writeln('<error>API response error: ' . $e->getMessage() . '</error>');

				return Command::FAILURE;
			} catch (ConnectionException $e) {
				$output->writeln('<error>Connection error: ' . $e->getMessage() . '</error>');

				return Command::FAILURE;
			}
		}

		return Command::SUCCESS;
	}

	/**
	 * @param string $contents
	 *
	 * @return Card[]
	 */
	private function splitFileToCards(string $contents): array {
		$cards = [];
		$chunks = preg_split('/^##\s*/m', $contents);

		foreach ($chunks as $chunk) {
			if (empty(trim($chunk))) {
				continue;
			}
			//echo "CHUNK:\n" . $chunk . "\n----\n";
			$chunk = trim($chunk);

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

			if (!empty($front) && !empty($back)) {
				$cards[] = new BasicCard($front, $back, $tags);
			}
		}

		return $cards;
	}
}
