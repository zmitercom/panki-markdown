<?php

declare(strict_types=1);

namespace App\Command\Card;

use App\Dto\BasicCard;
use App\Dto\ClozetCard;
use App\Exception\ApiResponseException;
use App\Exception\ConnectionException;
use App\Service\CardService;
use App\Service\RequestService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'card:add', description: 'Hello PhpStorm')]
class AddCommand extends Command
{
	public function __construct(
		private readonly RequestService $requestService,
		private readonly CardService    $cardService
	) {
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$io = new SymfonyStyle($input, $output);
		$deckName = 'ObsidianDeck';
		$text = 'This is a test card. {{c1::This is the answer}}';
		$tags = [
			'test',
			'phpstorm',
		];

		$clozetCard = new ClozetCard($text, $tags);
		$basicCard = new BasicCard('Just a basic card', 'This is the answer!', $tags);

		try {
			$this->cardService->addCardToAnki($deckName, $clozetCard);
			$io->success('Card added to Anki: ' . $clozetCard->getFront());

			$this->cardService->addCardToAnki($deckName, $basicCard);
			$io->success('Card added to Anki: ' . $basicCard->getFront());
		} catch (ApiResponseException $e) {
			$output->writeln('<error>API response error: ' . $e->getMessage() . '</error>');

			return Command::FAILURE;
		} catch (ConnectionException $e) {
			$output->writeln('<error>Connection error: ' . $e->getMessage() . '</error>');

			return Command::FAILURE;
		}

		return Command::SUCCESS;
	}
}
