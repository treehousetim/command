<?php namespace treehousetim\command;
// COPYRIGHT 2019 by Tim Gallagher
// see LICENSE file

class command
{
	protected $command = '';
	protected $args = [];
	protected $executed = false;
	protected $pipeCommand = null;
	protected $output = '';
	protected $outputFile = '';
	protected $outputAppendFile = false;
	protected $logger = null;
	protected $dryRun = false;
	public $execResults = [];
	public $commandLog = [];

	public function __construct( \Psr\Log\LoggerInterface $logger = null )
	{
		$this->logger = $logger;
	}
	//------------------------------------------------------------------------
	public static function factory( \Psr\Log\LoggerInterface $logger = null ) : command
	{
		return new command( $logger );
	}
	//------------------------------------------------------------------------
	public function command( string $command ) : self
	{
		$this->command = $command;
		return $this;
	}
	//------------------------------------------------------------------------
	public function subCommand( command $command ) : self
	{
		$this->args[] = $command;
		return $this;
	}
	//------------------------------------------------------------------------
	public function pipeCommand( command $command ) : self
	{
		if( $this->pipeCommand )
		{
			throw new \Exception( 'only one pipe can be defined per command - already defined as: ' . print_r( $this->pipeCommand, true ) );
		}

		if( $this->outputFile )
		{
			throw new \Exception( 'you cannot send output to a file and pipe output to a command for the same command.' );
		}

		$this->pipeCommand = $command;

		return $this;
	}
	//------------------------------------------------------------------------
	public function arg( string $arg ) : self
	{
		if( ! in_array( $arg, $this->args ) )
		{
			$this->args[] = $arg;
		}

		return $this;
	}
	//------------------------------------------------------------------------
	public function output( string $value ) : self
	{
		$this->output = $value;
		return $this;
	}
	//------------------------------------------------------------------------
	public function stdoutStderrCombine() : self
	{
		return $this->output( '2>&1' );
	}
	//------------------------------------------------------------------------
	public function outputAppendFile( string $filename ) : self
	{
		$this->outputFile( $filename );
		$this->outputAppendFile = true;

		return $this;
	}
	//------------------------------------------------------------------------
	public function outputOverwriteFile( string $filename ) : self
	{
		$this->outputFile( $filename );
		$this->outputAppendFile = false;

		return $this;
	}
	//------------------------------------------------------------------------
	public function outputFile( string $filename ) : self
	{
		if( $this->pipeCommand )
		{
			throw new \Exception( 'you cannot send output to a file and pipe output to a command for the same command.' );
		}

		$this->outputFile = $filename;

		return $this;
	}
	//------------------------------------------------------------------------
	public function getOutputFile() : string
	{
		return $this->outputFile;
	}
	//------------------------------------------------------------------------
	public function getExecutableString() : string
	{
		$out = $this->command;
		$args = [];

		foreach( $this->args as $_arg )
		{
			if( $_arg instanceof Command )
			{
				$arg = '"' . $_arg->getExecutableString() . '"';
			}
			else
			{
				$arg = escapeshellarg( $_arg );
			}

			$args[] = $arg;
		}
		$out .= ' ' . implode( ' ', $args );

		if( $this->output )
		{
			$out .= ' ' . $this->output;
		}

		if( $this->outputFile )
		{
			$redirection = ' > ';
			if( $this->outputAppendFile )
			{
				$redirection = ' >> ';
			}

			$out .= $redirection . $this->outputFile;
		}

		if( $this->pipeCommand )
		{
			$out .= ' | ' . $this->pipeCommand->getExecutableString();
		}

		return rtrim( $out );
	}
	//------------------------------------------------------------------------
	public function logger( \Psr\Log\LoggerInterface $logger ) : self
	{
		$this->logger = $logger;

		return $this;
	}
	//------------------------------------------------------------------------
	public function dryRun( bool $value ) : self
	{
		$this->dryRun = $value;

		return $this;
	}
	//------------------------------------------------------------------------
	public function exec() : string
	{
		$command = $this->getExecutableString();

		$execResult = [];

		if( $this->dryRun )
		{
			// return statuses are zero to indicate success
			$this->returnStatus = 0;
			$execResult[] = 'Dry Run. Not executed.';
		}
		else
		{
			exec( $command, $execResult, $this->returnStatus );
		}

		$retVal = implode( PHP_EOL, $execResult );

		$log = ['content' => $retVal, 'status' => $this->returnStatus, 'command' => $command];

		$this->execResults[] = $log;
		$this->commandLog[] = $command;

		if( $this->logger )
		{
			$this->logger->debug(
				PHP_EOL . 'Command: ' . $command . PHP_EOL .
				'Return Code: ' . $this->returnStatus . PHP_EOL .
				'Content: ' . PHP_EOL . $retVal . PHP_EOL . PHP_EOL .
				'------------------------------------------------------------------------' . PHP_EOL
			);
		}

		return $retVal;
	}
}
