<?php declare(strict_types=1);

use \treehousetim\command\command;
use PHPUnit\Framework\TestCase;

final class pipeTest extends TestCase
{
	protected function getCommand() : command
	{
		$command = new command();
		return $command
			->command( 'echo' )
			->arg( 'hello world' );
	}
	//------------------------------------------------------------------------
	public function testException()
	{
		$commandMore = new command();
		$commandMore->command( 'more' );

		$this->expectException( '\Exception' );

		$this->getCommand()
			->pipeCommand( $commandMore )
			->pipeCommand( $commandMore );
	}
	//------------------------------------------------------------------------
	public function testPipe()
	{
		$commandMore = new command();
		$commandMore->command( 'more' );

		$command = $this->getCommand()
			->pipeCommand( $commandMore );

		$this->assertEquals(
			"echo 'hello world' | more",
			$command->getExecutableString()
		);
	}
	//------------------------------------------------------------------------
	public function testSubCommandWithPipe()
	{
		$output = command::factory()
		->command( 'ls' )
			->arg( '-lah' )
		->pipeCommand(
			command::factory()
				->command( 'more' )
			)
		->getExecutableString();

		$this->assertEquals(
			"ls '-lah' | more",
			$output
		);
	}
}
