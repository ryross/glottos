<?php namespace PragmaRX\Glottos\Vendor\Laravel\Artisan;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ExportCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'glottos:export';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Export Glottos translations to Laravel lang files.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$exported = $this->laravel->glottos->export($this->laravel, $this->option('path'));

		if (is_null($exported)) $exported = 0;

		$this->info("$exported messages exported.");
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			// array('example', InputArgument::REQUIRED, 'An example argument.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('path', null, InputOption::VALUE_OPTIONAL, 'Path for output language directories.', null),
		);
	}

}
