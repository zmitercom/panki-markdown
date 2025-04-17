<?php

declare(strict_types=1);

namespace App\Command\Card;

use App\Exception\ApiResponseException;
use App\Exception\ConnectionException;
use App\Service\RequestService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'card:add', description: 'Hello PhpStorm')]
class AddCommand extends Command
{
	public function __construct(
		private readonly RequestService $requestService
	) {
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$deckName = 'ObsidianDeck';
		$text = 'This is a test card. {{c1::This is the answer}}';
		$tags = [
			'test',
			'phpstorm',
		];

		$response = $this->addCardToAnki($deckName, $text, $tags);

		return Command::SUCCESS;
	}

	/**
	 * @throws ApiResponseException
	 * @throws ConnectionException
	 */
	private function addCardToAnki(string $deckName, string $text, array $tags = []): void {
		$post = [
			'action' => 'addNote',
			'version' => 6,
			'params' => [
				'note' => [
					'deckName' => $deckName,
					'modelName' => 'Cloze',
					'fields' => [
						'Text' => $text,
					],
					'options' => [
						'allowDuplicate' => false,
					],
					'tags' => $tags,
				],
			],
		];

		$response = $this->requestService->do($post);

		if (isset($response['error'])) {
			throw new ApiResponseException('AnkiConnect error: ' . $response['error']);
		}
	}
}
