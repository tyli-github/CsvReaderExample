<?php

declare(strict_types=1);

namespace App\Command;

use Ajgl\Csv\Csv;
use Ajgl\Csv\Io\IoInterface;
use Ajgl\Csv\Reader\ReaderInterface;
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
class CsvReaderCommand extends Command
{
    /**
     * {@inheritDoc} 
     */
    protected function configure()
    {
        $this
            ->setName('csv:read')
            ->setDescription('Reads data from a CSV file.')
            ->addArgument('filename', InputArgument::REQUIRED, 'CSV file to read from.')
            ->addOption('charset', 0, InputOption::VALUE_REQUIRED, 'Default charset is "UTF-8"', 'UTF-8')
            ->addOption('delimiter', 0, InputOption::VALUE_REQUIRED, 'Default delimiter is ","', ',')
            ->addOption('max', 0, InputOption::VALUE_REQUIRED, 'Max rows to show', 100)
            ->addOption('no-headers', 0, InputOption::VALUE_NONE, 'Use this if CSV contains no headers as first row')
        ;
    }

    /**
     * {@inheritDoc} 
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // simply check if file exist
        $filename = $input->getArgument('filename');
        if (false === is_readable($filename)) {
            $output->writeln(sprintf('<error>File "%s" does not exist</error>', $filename));
            return 1;
        }

        // setup options
        $charset   = strtoupper($input->getOption('charset'));
        $delimiter = $input->getOption('delimiter');
        $noHeaders = $input->getOption('no-headers');
        $max       = (int)$input->getOption('max');

        // create CSV reader
        $reader  = $this->createReader($filename, $delimiter, $charset);
        
        // fetch first row to check if data is present (use array_filter to filter out empty results like [0 => NULL])
        $headers = $reader->readNextRow();
        if (!array_filter($headers)) {
            $output->writeln('<error>No rows found in CSV file</error>');
            return 1;
        }

        // add first row back if no headers
        if (true === $noHeaders) {
            $rows = $reader->readNextRows(IoInterface::CHARSET_DEFAULT, --$max);
            array_unshift($rows, $headers);
            $headers = [];
        } else {
            $rows = $reader->readNextRows(IoInterface::CHARSET_DEFAULT, $max);
        }
        
        // first row is used as headers
        $this->renderTable($output, $headers, $rows);

        return 0;
    }

    /**
     * Render data in a table with a progress bar.
     * @param OutputInterface $output
     * @param array $headers Headers of the table
     * @param array $rows Table data
     */
    protected function renderTable(OutputInterface $output, array $headers, array $rows)
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

    protected function createReader($filename, $delimiter, $charset, $readerType = 'rfc'): ReaderInterface
    {
        $csv = Csv::create();
        $csv->setDefaultReaderType($readerType);

        return $csv->createReader($filename, $delimiter, $charset);
    }
}
