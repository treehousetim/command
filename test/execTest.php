<?php declare(strict_types=1);

use treehousetim\command\command;
use PHPUnit\Framework\TestCase;

final class execTest extends TestCase
{
	public function testExec()
	{
		$command = new command();
		$output = $command
			->command( 'cat' )
			->arg( __FILE__ )
			->exec();

		$this->assertEquals( trim( file_get_contents( __FILE__ ) ), trim( $output ) );
	}
	//------------------------------------------------------------------------
	public function testDryRun()
	{
		$command = new command();
		$output = $command
			->command( 'ls' )
			->dryRun( true )
			->exec();

		$this->assertEquals( 'Dry Run. Not executed.', $output );
	}
}
