<?php declare(strict_types=1);

use treehousetim\command\command;
use PHPUnit\Framework\TestCase;

use Monolog\Handler\StreamHandler;

final class loggerTest extends TestCase
{
	public function testLogger()
	{
		$logFile = fopen( 'php://memory', 'w+' );

		$log = new \Monolog\Logger( 'Unit Tests' );
		//$handler = new StreamHandler( BASEPATH . 'test/testLog.log', \Monolog\Logger::DEBUG );

		$handler = new StreamHandler( $logFile, \Monolog\Logger::DEBUG );

		$formatter = new \Monolog\Formatter\LineFormatter(
			null, // Format of message in log, default [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n
			null, // Datetime format
			true, // allowInlineLineBreaks option, default false
			true  // discard empty Square brackets in the end, default false
		);

		$handler->setFormatter( $formatter );
		$log->pushHandler( $handler );

		$command = command::factory( $log )
			->command( 'ls' )
			->arg( '-lah' )
			->pipeCommand( command::factory()
				->command( 'more' )
			);

		$command->exec();

		rewind( $logFile );

		$logContent = '';

		while ( ($buffer = fgets($logFile) ) !== false)
		{
			$logContent .= $buffer;
		}

		$this->assertNotFalse( strpos( $logContent, "Command: ls '-lah' | more" ) );

		// $this->assertEquals(
		// 	"echo 'hello world' | more",
		// 	$command->exec()
		// );
	}
}
