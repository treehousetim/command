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

	public static function factory() : command
	{
		return new command();
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
			$out .= ' > ' . $this->outputFile;
		}

		if( $this->pipeCommand )
		{
			$out .= ' | ' . $this->pipeCommand->getExecutableString();
		}

		return rtrim( $out );
	}
}
