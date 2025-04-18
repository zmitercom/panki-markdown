<?php

declare(strict_types=1);

namespace App\Command\Media;

use App\Service\RequestService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'media:dir_path', description: 'Hello PhpStorm')]
class LocalDirPathCommand extends Command
{
	public function __construct(
		private readonly RequestService $requestService
	) {
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$io = new SymfonyStyle($input, $output);

		$response= $this->requestService->do(
			[
				'action' => 'getMediaDirPath',
				'version' => 6,
			]
		);
		dd($response);

		return Command::SUCCESS;
	}
}
