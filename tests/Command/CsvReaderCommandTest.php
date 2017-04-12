<?php
/**
 * @author Yuen Li <li.tsanyuen@gmail.com>
 */
namespace Tests\MyConsoleApp\Command;

use MyConsoleApp\Command\CsvReaderCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CsvReaderCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $fixturesDir = __DIR__;
    
    /**
     * @var string
     */
    protected $emptyCsvFile = '/Fixtures/empty.csv';
    
    /**
     * @var string
     */
    protected $validCsvFile = '/Fixtures/valid.csv';

    /**
     * {@inheritDoc} 
     */
    protected function setUp()
    {
        
    }

    /**
     * @covers MyConsoleApp\Command\CsvReaderCommand::configure
     * @covers MyConsoleApp\Command\CsvReaderCommand::execute
     */
    public function testInvalidFile()
    {
        $command = new CsvReaderCommand();
        $command->setApplication(new Application());
        
        $tester = new CommandTester($command);
        $tester->execute([
            'command'  => $command->getName(),
            'filename' => 'bla',
        ]);
        
        $this->assertRegExp('/File "bla" does not exist/', $tester->getDisplay());
    }
    
    /**
     * @covers MyConsoleApp\Command\CsvReaderCommand::configure
     * @covers MyConsoleApp\Command\CsvReaderCommand::execute
     * @covers MyConsoleApp\Command\CsvReaderCommand::createReader
     */
    public function testNoData()
    {
        $command = new CsvReaderCommand();
        $command->setApplication(new Application());
        
        $tester = new CommandTester($command);
        $tester->execute([
            'command'  => $command->getName(),
            'filename' => $this->fixturesDir . $this->emptyCsvFile,
        ]);
        
        $this->assertRegExp('/No rows found in CSV file/', $tester->getDisplay());
    }
    
    /**
     * @covers MyConsoleApp\Command\CsvReaderCommand::configure
     * @covers MyConsoleApp\Command\CsvReaderCommand::execute
     * @covers MyConsoleApp\Command\CsvReaderCommand::createReader
     * @covers MyConsoleApp\Command\CsvReaderCommand::renderTable
     */
    public function testMaxRows()
    {
        $command = new CsvReaderCommand();
        $command->setApplication(new Application());
        
        $tester = new CommandTester($command);
        $tester->execute([
            'command'  => $command->getName(),
            'filename' => $this->fixturesDir . $this->validCsvFile,
            '--max' => '1',
        ]);

        $output = $tester->getDisplay();
        $this->assertRegExp('/row1/', $output);
        $this->assertNotRegExp('/row2/', $output);
        $this->assertNotRegExp('/row3/', $output);
    }
    
    /**
     * @covers MyConsoleApp\Command\CsvReaderCommand::configure
     * @covers MyConsoleApp\Command\CsvReaderCommand::execute
     * @covers MyConsoleApp\Command\CsvReaderCommand::createReader
     * @covers MyConsoleApp\Command\CsvReaderCommand::renderTable
     */
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
        $this->assertRegExp('/row1/', $output);
        $this->assertRegExp('/row2/', $output);
        $this->assertRegExp('/row3/', $output);
    }
}
