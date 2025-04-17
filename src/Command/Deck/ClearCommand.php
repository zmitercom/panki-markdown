<?php

declare(strict_types=1);

namespace App\Command\Deck;

use App\Service\RequestService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'deck:clear', description: 'Clear all cards from a specific deck')]
class ClearCommand extends Command
{
	public function __construct(
		private readonly RequestService $requestService
	) {
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$deckName = 'ObsidianDeck'; // замените на нужное имя деки

		// Получаем ID всех карточек в деке
		$findCardsPayload = [
			'action' => 'findCards',
			'version' => 6,
			'params' => [
				'query' => "deck:\"$deckName\"",
			],
		];

		$cardsResponse = $this->requestService->do($findCardsPayload);

		if (!empty($cardsResponse['error'])) {
			$output->writeln('<error>Ошибка при получении карточек: ' . $cardsResponse['error'] . '</error>');

			return Command::FAILURE;
		}

		$cardIds = $cardsResponse['result'];

		// Преобразуем cardIds в noteIds
		$cardsToNotesPayload = [
			'action' => 'cardsToNotes',
			'version' => 6,
			'params' => [
				'cards' => $cardIds,
			],
		];

		$notesResponse = $this->requestService->do($cardsToNotesPayload);

		if (!empty($notesResponse['error'])) {
			$output->writeln('<error>Ошибка при получении noteIds: ' . $notesResponse['error'] . '</error>');

			return Command::FAILURE;
		}

		$noteIds = $notesResponse['result'];

		if (empty($cardIds)) {
			$output->writeln('<info>Нет карточек для удаления в деке "' . $deckName . '".</info>');

			return Command::SUCCESS;
		}

		if (!is_array($cardIds)) {
			$output->writeln('<error>Некорректный формат результата: ожидался массив noteIds.</error>');

			return Command::FAILURE;
		}

		// Удаляем карточки
		$deletePayload = [
			'action' => 'deleteNotes',
			'version' => 6,
			'params' => [
				'notes' => $noteIds,
			],
		];

		$deleteResponse = $this->requestService->do($deletePayload);

		if (!empty($deleteResponse['error'])) {
			$output->writeln('<error>Ошибка при удалении карточек: ' . $deleteResponse['error'] . '</error>');

			return Command::FAILURE;
		}

		$output->writeln('<info>Найдено ' . count($cardIds) . ' карточек в деке "' . $deckName . '".</info>');

		$output->writeln('<info>Удаляем карточки с noteIds: ' . implode(', ', $noteIds) . '</info>');

		$output->writeln('<info>Удаление завершено. Проверьте Anki для подтверждения.</info>');

		return Command::SUCCESS;
	}
}
