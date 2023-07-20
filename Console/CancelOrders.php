<?php
/*
 * Copyright © Improntus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Improntus\ProntoPaga\Console;

use Magento\Framework\Validation\ValidationException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;

use Improntus\ProntoPaga\Cron\CancelOrders as CancelOrdersCron;

class CancelOrders extends Command
{
    const TYPE = 'cron-type';
    const CRON_TYPES = ['1' => 'Cancel Orders'];

    /**
     * @var CancelOrdersCron
     */
    protected $cancelOrdersCron;

     /**
     * @var string
     */
    protected string $name;

    /**
     * @var string
     */
    protected string $type;

    /**
     * Constructor
     *
     * @param string|null $name
     * @param CancelOrdersCron $cancelOrdersCron
     */
    public function __construct(
        CancelOrdersCron $cancelOrdersCron,
        string $name = null,
    ) {
        parent::__construct($name);
        $this->cancelOrdersCron = $cancelOrdersCron;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $options = [
			new InputOption(
				self::TYPE,
				null,
				InputOption::VALUE_REQUIRED,
				"Run cron by code"
            )
		];

        $this->setName('prontopaga:cron:run');
        $this->setDescription('Run cron in interaction mode.');
        $this->setDefinition($options);
        parent::configure();
    }

    /**
     * Run cron in interaction mode.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        /** @var \Symfony\Component\Console\Helper\QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        if (!$input->getOption(self::TYPE)) {
            $question = new ChoiceQuestion("<question>Select executable cron: </question>", self::CRON_TYPES, '');
            $this->addNotEmptyValidator($question);
            $this->addOptionChoisedValidator($question);

            $input->setOption(
                self::TYPE,
                $questionHelper->ask($input, $output, $question)
            );

            $this->type = $input->getOption(self::TYPE);
        }
    }

    /**
     * Add not empty validator.
     *
     * @param \Symfony\Component\Console\Question\Question $question
     * @return void
     */
    private function addNotEmptyValidator(Question $question)
    {
        $question->setValidator(function ($value) {
            if (trim($value) == '') {
                throw new ValidationException(__('The value cannot be empty'));
            }

            return $value;
        });
    }

     /**
     * Add attribute type validator.
     *
     * @param \Symfony\Component\Console\Question\Question $question
     * @return void
     */
    private function addOptionChoisedValidator(Question $question)
    {
        $question->setValidator(function ($value) {
            if (trim($value) == '') {
                throw new ValidationException(__('The value cannot be empty'));
            }

            if (!array_key_exists($value, self::CRON_TYPES)) {
                throw new ValidationException(__('The value must be one of the options'));
            }

            return $value;
        });
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $cronType = $input->getOption(self::TYPE);
            $cronName = self::CRON_TYPES[$cronType];
            $output->writeln("<info>Executing {$cronName} Cron. </info>");

            switch ($cronType) {
                case '1':
                    $resultTime = $this->cancelOrdersCron->execute();
                    break;
                default:
                    $output->writeln("<error>Cron not found.</error>");
                    return 1;
            }

        } catch (\Exception $th) {
            $output->writeln("<error>{$th->getMessage()}</error>");
            return 1;
        }

        $output->writeln(
            __("<info>Cron {$cronName} has been finished successfully in %time</info>", ['time' => gmdate('H:i:s', (int) $resultTime ?? 0)])
        );

        return 0;
    }
}
