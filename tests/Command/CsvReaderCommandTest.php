<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\CsvReaderCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CsvReaderCommandTest extends TestCase
{
    protected string $fixturesDir = __DIR__;

    protected string $emptyCsvFile = '/Fixtures/empty.csv';

    protected string $validCsvFile = '/Fixtures/valid.csv';

    public function testInvalidFile()
    {
        $command = new CsvReaderCommand();
        $command->setApplication(new Application());
        
        $tester = new CommandTester($command);
        $tester->execute([
            'command'  => $command->getName(),
            'filename' => 'bla',
        ]);
        
        $this->assertMatchesRegularExpression('/File "bla" does not exist/', $tester->getDisplay());
    }

    public function testNoData()
    {
        $command = new CsvReaderCommand();
        $command->setApplication(new Application());
        
        $tester = new CommandTester($command);
        $tester->execute([
            'command'  => $command->getName(),
            'filename' => $this->fixturesDir . $this->emptyCsvFile,
        ]);
        
        $this->assertMatchesRegularExpression('/No rows found in CSV file/', $tester->getDisplay());
    }

    public function testMaxRows()
    {
        $command = new CsvReaderCommand();
        $command->setApplication(new Application());
        
        $tester = new CommandTester($command);
        $tester->execute([
            'command'      => $command->getName(),
            'filename'     => $this->fixturesDir . $this->validCsvFile,
            '--max'        => '1',
            '--no-headers' => null, // no headers in file
        ]);

        $output = $tester->getDisplay();
        $this->assertMatchesRegularExpression('/row1/', $output);
        $this->assertDoesNotMatchRegularExpression('/row2/', $output);
        $this->assertDoesNotMatchRegularExpression('/row3/', $output);
    }

    public function testTableOutput()
    {
        $command = new CsvReaderCommand();
        $command->setApplication(new Application());
        
        $tester = new CommandTester($command);
        $tester->execute([
            'command'  => $command->getName(),
            'filename' => $this->fixturesDir . $this->validCsvFile,
        ]);

        $output = $tester->getDisplay();
        $this->assertMatchesRegularExpression('/row1/', $output);
        $this->assertMatchesRegularExpression('/row2/', $output);
        $this->assertMatchesRegularExpression('/row3/', $output);
    }
}
