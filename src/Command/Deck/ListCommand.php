<?php

declare(strict_types=1);

namespace App\Command\Deck;

use App\Service\RequestService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'deck:list', description: 'List all decks')]
class ListCommand extends Command
{
	public function __construct(
		private readonly RequestService $requestService
	) {
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$post = [
			'action' => 'deckNames',
			'version' => 6,
		];
		$response = $this->requestService->do($post);
		// "{"result": ["07. \u041f\u0430\u0441\u0441\u0430\u0436\u0438\u0440\u044b", "14000 v2.3", "Custom Study Session", "English Everyday Idioms", "English Irregular Verbs in Context \u266a", "J\u0119zyk polski", "J\u0119zyk polski::Lekcja 1", "J\u0119zyk polski::Lekcja 1::Lekcja 1-1", "J\u0119zyk polski::Lekcja 1::Lekcja 1-2", "J\u0119zyk polski::Lekcja 1::Lekcja 1-3", "J\u0119zyk polski::Lekcja 10", "J\u0119zyk polski::Lekcja 11", "J\u0119zyk polski::Lekcja 12", "J\u0119zyk polski::Lekcja 13", "J\u0119zyk polski::Lekcja 14", "J\u0119zyk polski::Lekcja 15", "J\u0119zyk polski::Lekcja 16", "J\u0119zyk polski::Lekcja 17", "J\u0119zyk polski::Lekcja 18", "J\u0119zyk polski::Lekcja 19", "J\u0119zyk polski::Lekcja 2", "J\u0119zyk polski::Lekcja 20", "J\u0119zyk polski::Lekcja 21", "J\u0119zyk polski::Lekcja 22", "J\u0119zyk polski::Lekcja 23", "J\u0119zyk polski::Lekcja 24", "J\u0119zyk polski::Lekcja 25", "J\u0119zyk polski::Lekcja 26", "J\u0119zyk polski::Lekcja 27", "J\u0119zyk polski::Lekcja 28", "J\u0119zyk polski::Lekcja 29", "J\u0119zyk polski::Lekcja 3", "J\u0119zyk polski::Lekcja 30", "J\u0119zyk polski::Lekcja 4", "J\u0119zyk polski::Lekcja 4::Lekcja 4-1", "J\u0119zyk polski::Lekcja 4::Lekcja 4-2", "J\u0119zyk polski::Lekcja 4::Lekcja 4-3", "J\u0119zyk polski::Lekcja 5", "J\u0119zyk polski::Lekcja 6", "J\u0119zyk polski::Lekcja 7", "J\u0119zyk polski::Lekcja 9", "Kruzo RU-EN Words x1000 1", "My cloze words", "My English", "ObsidianDeck", "PHP - Top 100 functions", "Polish", "Polish - something..", "SSE 4000 Essential English Words by Paul Nation", "SSE 4000 Essential English Words by Paul Nation::Book 1", "SSE 4000 Essential English Words by Paul Nation::book 1 extra words", "SSE 4000 Essential English Words by Paul Nation::book 2", "SSE 4000 Essential English Words by Paul Nation::book 2 extra words", "SSE 4000 Essential English Words by Paul Nation::book 3", "SSE 4000 Essential English Words by Paul Nation::book 3 extra words", "SSE 4000 Essential English Words by Paul Nation::book 4", "SSE 4000 Essential English Words by Paul Nation::book 5", "SSE 4000 Essential English Words by Paul Nation::book 6", "SSE Phrasal Verbs", "SSH", "\u041f\u043e \u0443\u043c\u043e\u043b\u0447\u0430\u043d\u0438\u044e"], "error": null}"

		$result = $response['result'] ?? [];
		if (empty($result)) {
			$output->writeln('<error>No decks found.</error>');
			return Command::FAILURE;
		}

		$output->writeln('<info>Decks:</info>');

		foreach ($result as $deck) {
			// write as list (tabs or spaces)
			$output->writeln(' - ' . $deck);
		}
		$output->writeln('<info>Done.</info>');

		return Command::SUCCESS;
	}
}
