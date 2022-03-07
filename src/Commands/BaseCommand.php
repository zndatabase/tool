<?php

namespace ZnDatabase\Tool\Commands;

use ZnLib\Fixture\Domain\Services\FixtureService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

abstract class BaseCommand extends Command
{

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->addOption(
                'withConfirm',
                null,
                InputOption::VALUE_REQUIRED,
                'Your selection migrations',
                true
            );
    }

    protected function isContinueQuestion(string $question, InputInterface $input, OutputInterface $output): bool
    {
        $withConfirm = $input->getOption('withConfirm');
        if ( ! $withConfirm) {
            return true;
        }
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion($question . ' (y|N): ', false);
        return $helper->ask($input, $output, $question);
    }
}
