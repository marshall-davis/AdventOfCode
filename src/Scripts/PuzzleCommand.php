<?php

namespace Marshall\AdventOfCode\Scripts;

use Carbon\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PuzzleCommand extends Command
{
    protected function configure(): void
    {

    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = Carbon::today()->format('Y/d');
        $output->writeln($path);
        if (!is_dir($path)) {
            mkdir($path, recursive: true);
        }
        touch("{$path}/elmo.txt");

        return Command::SUCCESS;
    }
}