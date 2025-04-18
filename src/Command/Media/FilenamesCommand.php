<?php

declare(strict_types=1);

namespace App\Command\Media;

use App\Service\RequestService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'media:filenames', description: 'Hello PhpStorm')]
class FilenamesCommand extends Command
{
	public function __construct(
		private readonly RequestService $requestService
	) {
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$io = new SymfonyStyle($input, $output);

		// Gets the names of media files matched the pattern. Returning all names by default.
		// {
		//    "action": "getMediaFilesNames",
		//    "version": 6,
		//    "params": {
		//        "pattern": "_hell*.txt"
		//    }
		//}

		$pattern = '*';
		$io->writeln('Pattern: ' . $pattern);
		$response= $this->requestService->do(
			[
				'action' => 'getMediaFilesNames',
				'version' => 6,
				'params' => [
					'pattern' => $pattern,
				],
			]
		);
		dd($response);

		return Command::SUCCESS;
	}
}
