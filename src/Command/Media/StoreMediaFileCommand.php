<?php

declare(strict_types=1);

namespace App\Command\Media;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'media:store_file', description: 'Hello PhpStorm')]
class StoreMediaFileCommand extends Command
{
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$io = new SymfonyStyle($input, $output);

		//

		return Command::SUCCESS;
	}
}
