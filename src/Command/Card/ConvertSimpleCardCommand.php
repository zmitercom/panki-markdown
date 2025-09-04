<?php

declare(strict_types=1);

namespace App\Command\Card;

use App\Exception\ApiResponseException;
use App\Exception\ConnectionException;
use App\Service\CardService;
use App\Service\MarkdownService;
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
		private readonly RequestService  $requestService,
		private readonly CardService     $cardService,
		private readonly MarkdownService $markdownService,
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

		$cards = $this->markdownService->splitFileToCards($contents);
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
			} catch (ConnectionException $e) {
				$output->writeln('<error>Connection error: ' . $e->getMessage() . '</error>');
			}
		}

		return Command::SUCCESS;
	}

}
