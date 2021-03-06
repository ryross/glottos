<?php namespace PragmaRX\Glottos\Support;
/**
 * Part of the Glottos package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Glottos
 * @version    1.0.0
 * @author     Antonio Carlos Ribeiro @ PragmaRX
 * @license    BSD License (3-clause)
 * @copyright  (c) 2013, PragmaRX
 * @link       http://pragmarx.com
 */

use PragmaRX\Glottos\Support\Filesystem;

class Config {

	protected $config;

	protected $isAppConfig = false;

	/**
	 * Create a new configuration repository
	 * 
	 * @param Filesystem $files 
	 * @param void
	 */
	public function __construct(Filesystem $files, $app = null)
	{
		$this->app = $app;

		$this->files = $files;

		$this->loadConfig();
	}

	/**
	 * Get the specified configuration value
	 * @param  string $key     
	 * @param  string $default value
	 * @return string
	 */
	public function get($key, $default = null)
	{
		if ($this->isAppConfig)
		{
			return $this->app['config']["pragmarx/glottos::$key"]; // is there a better way than hard coding this?
		}

		if ( ! isset($this->config[$key]))
		{
			return $default;
		}

		return $this->config[$key];
	}

	/**
	 * Load the configuration group
	 * 
	 * @return void
	 */
	public function loadConfig()
	{
		if (isset($this->app) && $this->app['config'])
		{
			$this->isAppConfig = true;
		}
		else
		{
			$this->config = $this->files->getRequire(__DIR__.'/../../../config/config.php');
		}
	}
}