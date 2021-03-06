<?php namespace PragmaRX\Glottos\Repositories;
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

use PragmaRX\Glottos\Support\Sentence;
use PragmaRX\Glottos\Support\Locale;
use PragmaRX\Glottos\Support\Config;
use PragmaRX\Glottos\Support\Filesystem;
use PragmaRX\Glottos\Support\Finder;

use PragmaRX\Glottos\Repositories\Messages\MessageInterface;
use PragmaRX\Glottos\Repositories\Messages\TranslationInterface;
use PragmaRX\Glottos\Repositories\Locales\LocaleRepositoryInterface;

class DataRepository implements DataRepositoryInterface {

	private $message;

	private $translation;

	private $localeRepository;

	/**
	 * Create instance of DataRepository
	 * @param MessageInterface          $message          
	 * @param TranslationInterface      $translation      
	 * @param LocaleRepositoryInterface $localeRepository 
	 * @param Config                    $config           
	 * @param Filesystem                $fileSystem       
	 * @param Finder                    $finder           
	 */
	public function __construct(
									MessageInterface $message, 
									TranslationInterface $translation, 
									LocaleRepositoryInterface $localeRepository, 
									Config $config,
									Filesystem $fileSystem,
									Finder $finder
								)
	{
		$this->message = $message;

		$this->translation = $translation;

		$this->localeRepository = $localeRepository;

		$this->config = $config;

		$this->fileSystem = $fileSystem;

		$this->finder = $finder;
	}

	/**
	 * Find a message
	 * 
	 * @param  Sentence $sentence 
	 * @return Sentence
	 */
	public function findMessage(Sentence $sentence)
	{
		if (! $sentence->getId())
		{
			$sentence = $this->message->find($sentence);
		}

		return $sentence;
	}

	/**
	 * Find a translation
	 * 
	 * @param  Sentence $sentence 
	 * @param  Locale   $locale   
	 * @return Sentence
	 */
	public function findTranslation(Sentence $sentence, Locale $locale)
	{
		$sentence = $this->findMessage($sentence);

		$translation = $this->translation->find($sentence, $locale);

		if ($translation->translationFound && $this->config->get('debug'))
		{
			$translation->setTranslation(str_repeat($this->config->get('debug_character'), strlen($translation->getTranslation())));
		}

		if (! $translation->translationFound && $sentence->getMode() == 'natural' && $locale->is($this->getDefaultLocale()))
		{
			$translation = $this->addTranslation($sentence, $this->getDefaultLocale());
		}

		return $translation;
	}

	/**
	 * Find a translation by id
	 * 
	 * @param  integer $message_id 
	 * @param  Locale $locale     
	 * @return object|null
	 */
	public function findTranslationById($message_id, Locale $locale)
	{
		return $this->translation->findById($message_id, $locale);
	}

	/**
	 * Find a message by id
	 * 
	 * @param  integer $message_id 
	 * @return object|null
	 */
	public function findMessageById($message_id)
	{
		return $this->message->findById($message_id);
	}

	/**
	 * Get the default locale
	 * 
	 * @return Locale
	 */
	public function getDefaultLocale()
	{
		return new Locale($this->config->get('default_language_id'), $this->config->get('default_country_id'));
	}

	/**
	 * Guess the secondary locale to be used by Glottos
	 * 
	 * @param  mixed $primaryLocale 
	 * @return string
	 */
	public function guessSecondaryLocale($primaryLocale)
	{
		return 'pt-br';
	}

	/**
	 * Add a translation
	 * 
	 * @param Sentence $translation 
	 * @param Locale   $locale      
	 */
	public function addTranslation(Sentence $translation, Locale $locale)
	{
		$this->translation->add($translation, $locale);

		return $translation;
	}

	/**
	 * Check if a particular locale is enabled
	 * 
	 * @param  mixed $locale 
	 * @return bool
	 */
	public function localeIsAvailable($locale)
	{
		if ($this->getDefaultLocale()->is($locale))
		{
			return true;
		}

		return $this->localeRepository->localeIsAvailable(Locale::make($locale));
	}

	/**
	 * Get all languages
	 * 
	 * @return object|null
	 */
	public function getAllLanguages()
	{
		return $this->localeRepository->getLanguages();
	}

	/**
	 * Get all enabled languages
	 * 
	 * @return object|null
	 */
	public function getEnabledLanguages()
	{
		return $this->localeRepository->getLanguages('enabled', '=', true);
	}

	/**
	 * Get all disabled languages
	 * 
	 * @return object|null
	 */
	public function getDisabledLanguages()
	{
		return $this->localeRepository->getLanguages('enabled', '=', false);
	}

	/**
	 * Enable or disable a language
	 * @param  integer $id     
	 * @param  bool $enable 
	 * @return bool
	 */
	public function enableDisableLanguage($id, $enable)
	{
		return $this->localeRepository->enableDisableLanguage($id, $enable);
	}

	/**
	 * Get a result set of language stats
	 * @return object|null
	 */
	public function getLanguageStats()
	{
		return $this->localeRepository->getLanguageStats();
	}

	/**
	 * Get all translations
	 * 
	 * @param  Locale $localePrimary   
	 * @param  Locale $localeSecondary 
	 * @return object|null
	 */
	public function getTranslations(Locale $localePrimary = null, Locale $localeSecondary = null)
	{
		return $this->translation->getAll($localePrimary, $localeSecondary);
	}

	/**
	 * Find a locale in the repository
	 * 
	 * @param  Locale $locale 
	 * @return object|null
	 */
	public function findLocale(Locale $locale)
	{
		 return $this->localeRepository->find($locale);
	}

	/**
	 * Update or create a translation
	 * 
	 * @param  string $message           
	 * @param  string $translatedMessage 
	 * @param  Locale $locale            
	 * @param  string $domain            
	 * @param  string $mode              
	 * @return object
	 */
	public function updateOrCreateTranslation($message, $translatedMessage, Locale $locale, $domain, $mode)
	{
		$this->translation->updateOrCreate($message, $translatedMessage, $domain, $locale, $mode);
	}

	/**
	 * Delete a translation
	 * 
	 * @param  string $message           
	 * @param  Locale $locale            
	 * @param  string $domain            
	 * @return object
	 */
	public function deleteTranslation($message, Locale $locale, $domain)
	{
		$this->translation->delete($message, $domain, $locale);
	}
	
	/**
	 * Find the next untranslated message
	 * @param  Locale $primaryLocale   
	 * @param  Locale $secondaryLocale 
	 * @return object|null
	 */
	public function findNextUntranslated(Locale $primaryLocale, Locale $secondaryLocale)
	{
		return $this->translation->findNextUntranslated($primaryLocale, $secondaryLocale);
	}	

	/**
	 * Import Laravel language files to Glottos
	 * 
	 * @param  object $app    
	 * @param  string $path   
	 * @param  string $domain 
	 * @param  string $mode   
	 * @return int
	 */
	public function import($app, $path, $domain, $mode)
	{
		if ( ! $path)
		{
			$path = $app['path.base'].'/app/lang';
		}

		$locales = $this->fileSystem->directories($path);

		$imported = 0;

		foreach($locales as $locale)
		{
			$imported += $this->importLocale(basename($locale), dirname($locale), $domain, $mode);
		}

		return $imported;
	}

	/**
	 * Import one laravel message to Glottos
	 * 
	 * @param  string $locale 
	 * @param  string $path   
	 * @param  string $domain 
	 * @param  string $mode   
	 * @return bool
	 */
	private function importLocale($locale, $path, $domain, $mode)
	{
		$imported = 0;

		$locale = Locale::make($locale);

		$finder = $this->finder->create();
		foreach($finder->files()->in($path.'/'.$locale->getText()) as $file)
		{
			$values = $this->fileSystem->getRequire($file);

			$group = str_replace('.php', '', basename($file));

			foreach ($values as $key => $value)
			{
				$imported += $this->importLocaleKey($group, $key, $value, $domain, $locale, $mode);
			}
		}

		return $imported;
	}

	private function importLocaleKey($group, $key, $value, $domain, $locale, $mode)
	{
		$imported = 0;

		if (is_array($value))
		{
			foreach ($value as $sub_key => $sub_value)
			{
				$imported += $this->importLocaleKey($group, $key .'.'.$sub_key, $sub_value, $domain, $locale, $mode);
			}
		}
		else
		{
			if ($this->addKey($group, $key, $value, $domain, $locale, $mode))
			{
				$imported++;
			}
		}
		return $imported;
	}


	/**
	 * Add translation key to data repository
	 * 
	 * @param string $group  
	 * @param string $key    
	 * @param string $value  
	 * @param string $domain 
	 * @param string $locale 
	 * @param string $mode   
	 */
	private function addKey($group, $key, $value, $domain, $locale, $mode)
	{

		$translation = Sentence::makeTranslation(
												"key::$group.$key",
												$value,
												$domain,
												$mode
											);

		$translation = $this->findTranslation($translation, $locale);

		if (! $translation->translationFound)
		{
			$this->addTranslation($translation, $locale);

			return true;
		}

		return false;
	}


	/**
	 * Export Glottos translations to Laravel language files
	 * 
	 * @param  object $app    
	 * @param  string $path   
	 * @param  string $domain 
	 * @param  string $mode   
	 * @return int
	 */
	public function export($app, $path, $domain, $mode)
	{
		if ( ! $path)
		{
			$path = $app['path.base'].'/app/lang';
		}

		$languages = $this->getEnabledLanguages();

		$total_exported = 0;

		foreach($languages as $lang)
		{
			$lang_name = $lang->language_id . ($lang->country_id ? '_' . $lang->country_id : '');
			$dir = $path.'/'.$lang_name;
			
			if ( ! $this->fileSystem->exists($dir) || ! $this->fileSystem->isDirectory($dir))
			{
				$this->fileSystem->makeDirectory($dir);
			}

			$exported = $this->exportLocale($lang_name, $dir, $domain, $mode);
			foreach ($exported as $group => $export_count)
			{
				$total_exported += $export_count;
			}

			#make sure groups with no translations for this locale are deleted
			$existing_groups = $this->fileSystem->files($dir);
			foreach ($existing_groups as $group_file)
			{
				$group = basename($group_file, '.php');
				if ( ! isset($exported[$group]) || $exported[$group] == 0)
				{
					$this->fileSystem->delete($group_file);
				}
			}
		}

		return $total_exported;
	}

	/**
	 * Exort Glottos messages for one locale to Laravel lang files
	 * 
	 * @param  string $locale 
	 * @param  string $path   
	 * @param  string $domain 
	 * @param  string $mode   
	 * @return bool
	 */
	private function exportLocale($locale, $path, $domain, $mode)
	{
		$locale = Locale::make($locale);

		$locale_translations = $this->translation->getAllForOneLocale($locale);

		$exported = array();
		$groups = array();

		foreach ($locale_translations as $translation)
		{
			if ($translation->key == NULL || $translation->message == NULL)
			{
				continue;
			}

			$parts = explode('.', $translation->key, 2);

			if (count($parts) == 2)
			{
				if ( ! isset($groups[$parts[0]]))
				{
					$groups[$parts[0]] = array();
					$exported[$parts[0]] = 0;
				}
				$groups[$parts[0]][$parts[1]] = $translation->message;
				$exported[$parts[0]]++;
			}
			else
			{
				#skipping translations without keys since we don't know where to put them.
			}
		}

		foreach ($groups as $group => $values)
		{
			$file = $path . '/' . $group . '.php';
			$output = "<?php\nreturn " . var_export($values, true) . ";\n";
			$this->fileSystem->put($file, $output);
		}

		return $exported;
	}
	

	/**
	 * Get all translations for one locale
	 * 
	 * @param  $localePrimary   
	 * @return object|null
	 */
	public function getLocaleTranslations($localePrimary = null)
	{
		$locale = Locale::make($localePrimary);
		return $this->translation->getAllForOneLocale($locale);
	}
}
