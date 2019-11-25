# treehousetim/command
simple, dependency-free, uncomplicated shell scripting command class 

[![Build Status](https://api.travis-ci.org/treehousetim/command.svg)](https://travis-ci.org/treehousetim/command)

[TOC]

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

```

## Executing your command
Simply call `$command->exec();` to execute your command.

Each time `->exec();` is called, an entry in the `$command->execResults` array is created.

### $command->execResults array

The format of `$command->execResults[]` is as follows:

```
[
	'content' => $content,
	'status' => $returnStatus,
	'command' => $command
];
```

If all you want is a list of commands that have been executed, without the other array entries you may use `$command->commandLog[]`.

### Command log

```
echo $command->commandLog[0];
```


The output from your command is returned from the `exec()` method.


## Logging
Command executions may be logged.  To set up logging, pass any `\Psr\Log\LoggerInterface` object to the constructor or the factory method on `\treehousetim\command\command` to log executions.

### Example using Monolog:

```
<?php

use \treehousetim\command\command;

$log = new \Monolog\Logger( 'My Example Program' );
$log->pushHandler( new StreamHandler( './example.log', \Monolog\Logger::DEBUG ) );

$command = command::factory( $log )
	->command( 'ls' )
	->arg( '-lah' )
	->exec();

$output = $command->exec();

echo $command->commandLog[0];
echo PHP_EOL;
echo $output

?>
```

## Testing the codebase
If you have cloned this repo, you can run the tests.

Run composer install to install PHPUnit and Monolog.

1. `composer install`
2. `./vendor/bin/phpunit test`
