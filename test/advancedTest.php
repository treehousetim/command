<?php declare(strict_types=1);

use \treehousetim\command\command;
use PHPUnit\Framework\TestCase;

final class advancedTest extends TestCase
{
	public function testSubCommand()
	{
		$connection = 'user@example.com';
		$loginPath = 'mylogin';
		$db = 'my_database';
		$tables = ['table1', 'table2'];
		$where = 'id > 1200';

		$output = command::factory()
		->command( 'ssh' )
			->arg( $connection )
		->subCommand(
			command::factory()
				->command( '/usr/bin/mysqldump' )
				->arg( '--login-path=' . $loginPath )
				->arg( '--skip-add-drop-table' )
				->arg( '--no-create-info' )
				->arg( $db )
				->arg( implode( ',', $tables ) )
				->arg( '--where=' . $where )
				->stdoutStderrCombine()
			)
		->stdoutStderrCombine()
		->outputFile( '/path/to/file.sql' )
		->getExecutableString();

		$this->assertEquals( "ssh 'user@example.com' \"/usr/bin/mysqldump '--login-path=mylogin' '--skip-add-drop-table' '--no-create-info' 'my_database' 'table1,table2' '--where=id > 1200' 2>&1\" 2>&1 > /path/to/file.sql",
		$output );
	}
	//------------------------------------------------------------------------
	public function testAppendOutput()
	{
		$connection = 'user@example.com';
		$loginPath = 'mylogin';
		$db = 'my_database';
		$tables = ['table1', 'table2'];
		$where = 'id > 1200';

		$output = command::factory()
		->command( 'ssh' )
			->arg( $connection )
		->subCommand(
			command::factory()
				->command( '/usr/bin/mysqldump' )
				->arg( '--login-path=' . $loginPath )
				->arg( '--skip-add-drop-table' )
				->arg( '--no-create-info' )
				->arg( $db )
				->arg( implode( ',', $tables ) )
				->arg( '--where=' . $where )
				->stdoutStderrCombine()
			)
		->stdoutStderrCombine()
		->outputAppendFile( '/path/to/file.sql' )
		->getExecutableString();

		$this->assertEquals( "ssh 'user@example.com' \"/usr/bin/mysqldump '--login-path=mylogin' '--skip-add-drop-table' '--no-create-info' 'my_database' 'table1,table2' '--where=id > 1200' 2>&1\" 2>&1 >> /path/to/file.sql",
		$output );
	}
}