<?php

declare(strict_types=1);

namespace App\Command;

use League\Csv\Reader;
use League\Csv\Statement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CSV Reader command for console.
 */
final class CsvReaderCommand extends Command
{
    private const string OPEN_MODE = 'r';

    protected function configure(): void
    {
        $this
            ->setName('csv:read')
            ->setDescription('Reads data from a CSV file.')
            ->addArgument('filename', InputArgument::REQUIRED, 'CSV file to read from.')
            ->addOption('delimiter', null, InputOption::VALUE_REQUIRED, 'Default delimiter is ","', ',')
            ->addOption('max', null, InputOption::VALUE_REQUIRED, 'Max rows to show', 100)
            ->addOption('no-headers', null, InputOption::VALUE_NONE, 'Use this if CSV contains no headers as first row')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = $input->getArgument('filename');
        if (false === is_readable($filename)) {
            $output->writeln(sprintf('<error>File "%s" does not exist</error>', $filename));
            return 1;
        }

        // setup options
        $delimiter = $input->getOption('delimiter');
        $noHeaders = $input->getOption('no-headers');
        $max = (int)$input->getOption('max');

        $csv = Reader::from($filename, self::OPEN_MODE);
        $csv->setDelimiter($delimiter);

        if ($csv->count() === 0) {
            $output->writeln('<info>No rows found in CSV file</info>');
            return 0;
        }

        $headers = [];
        if (true !== $noHeaders) {
            $csv->setHeaderOffset(0);
            $headers = $csv->getHeader();
        }

        $stmt = new Statement();
        $stmt = $stmt->limit($max);
        $records = $stmt->process($csv);
        $rows = array_values(array_map(function (array $row) {
            return $row;
        }, iterator_to_array($records)));

        $this->renderTable($output, $headers, $rows);

        return 0;
    }

    protected function renderTable(OutputInterface $output, array $headers, array $rows): void
    {
        $table = new Table($output);
        
        if (!empty($headers)) {
            $table->setHeaders($headers);
        }

        $rowCount = count($rows);

        $progressBar = new ProgressBar($output, $rowCount);
        $progressBar->setProgressCharacter("\xF0\x9F\x8D\xBA");
        for ($i=0; $i<$rowCount; ++$i) {
            $table->addRow($rows[$i]);
            usleep(600000); // only for demo purposes
            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln('');
        $table->render();
    }
}
