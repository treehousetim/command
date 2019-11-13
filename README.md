# treehousetim/command
simple, dependency-free, uncomplicated shell scripting command class

## Installing

`composer require treehousetim/command`

## Usage

### Simple command with argument
```
<?php

$command = new \treehousetim\command\command();
$command
	->command( 'ls' )
	->arg( '-lah' );

echo $command->getExecutableString();

// output:
// ls '-lah'
?>
```

### Simple command that is piped
Notice the use of the factory method here to make code pretty.

```
<?php

echo \treehousetim\command\command::factory()
->command( 'ls' )
	->arg( '-lah' )
->pipeCommand(
	command::factory()
		->command( 'more' )
	)
->getExecutableString();

// output:
// ls '-lah' | more

?>
```

## Advanced Usage
treehousetim/command supports sub commands, combining stderr and stdout and output redirection to a file.

```
<?php

use \treehousetim\command\command;

$connection = 'user@example.com';
$loginPath = 'mylogin';
$db = 'my_database';
$tables = ['table1', 'table2'];
$where = 'id > 1200';

echo command::factory()
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

// output
// ssh 'user@example.com' "/usr/bin/mysqldump '--login-path=mylogin' '--skip-add-drop-table' '--no-create-info' 'my_database' 'table1,table2' '--where=id > 1200' 2>&1" 2>&1 > /path/to/file.sql


// minor difference: outputAppendFile - uses >> instead of >
echo command::factory()
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

// output
// ssh 'user@example.com' "/usr/bin/mysqldump '--login-path=mylogin' '--skip-add-drop-table' '--no-create-info' 'my_database' 'table1,table2' '--where=id > 1200' 2>&1" 2>&1 >> /path/to/file.sql



?>
```

## Testing
If you have cloned this repo, you can run the tests.
There are no dependencies, but PHPUnit is installed with composer.

1. `composer install`
2. `./vendor/bin/phpunit test`
