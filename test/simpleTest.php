<?php declare(strict_types=1);

use \treehousetim\command\command;
use PHPUnit\Framework\TestCase;

final class simpleTest extends TestCase
{
	//------------------------------------------------------------------------
	public function testNoArguments()
	{
		$command = new command();
		$command
			->command( 'echo' )
			->arg( 'hello world' );

		$this->assertEquals(
			"echo 'hello world'",
			$command->getExecutableString()
		);
	}
	//------------------------------------------------------------------------
	public function testLs()
	{
		$command = new command();
		$command
			->command( 'ls' )
			->arg( '-l' )
			->arg( '-a' )
			->arg( '-h' );

		$this->assertEquals(
			"ls '-l' '-a' '-h'",
			$command->getExecutableString()
		);
	}
}
