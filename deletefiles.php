<?php
/**
 * @package       Joomla.Plugins
 * @subpackage    Task.Testtasks
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

/** A demo Task plugin for com_scheduler. */

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

/**
 * The plugin class
 *
 * @since __DEPLOY__VERSION__
 */
class PlgTaskDeletefiles extends CMSPlugin implements SubscriberInterface
{
	use TaskPluginTrait;

	/**
	 * @var string[]
	 * @since __DEPLOY_VERSION__
	 */
	private const TASKS_MAP = [
		'deletefiles'         => [
			'langConstPrefix' => 'PLG_TASK_DELETEFILES_TASKS',
			'call'            => 'deletefiles',
			'form'            => 'deletefilesTaskForm'
		]
	];

	/**
	 * Autoload the language file
	 *
	 * @var boolean
	 * @since __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * An array of supported Form contexts
	 *
	 * @var string[]
	 * @since __DEPLOY_VERSION__
	 */
	private $supportedFormContexts = [
		'com_scheduler.task'
	];

	/**
	 * Returns event subscriptions
	 *
	 * @return string[]
	 *
	 * @since __DEPLOY__
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onTaskOptionsList'    => 'advertiseRoutines',
			'onExecuteTask'        => 'routineHandler',
			'onContentPrepareForm' => 'manipulateForms'
		];
	}

	/**
	 * @param   ExecuteTaskEvent  $event  onExecuteTask Event
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since  __DEPLOY_VERSION
	 */
	public function routineHandler(ExecuteTaskEvent $event): void
	{
		if (!array_key_exists($routineId = $event->getRoutineId(), self::TASKS_MAP))
		{
			return;
		}

		$this->taskStart($event);

		// Access to task parameters
		$params = $event->getArgument('params');
		$timeout = $params->timeout ?? 1;
		$timeout = ((int) $timeout) ?: 1;

		// Plugin does whatever it wants

		if (array_key_exists('call', self::TASKS_MAP[$routineId]))
		{
//			$this->{self::TASKS_MAP[$routineId]['call']}();

			initWithParameters($argv);

			if ($debugMode) {
				error_reporting(E_ALL);
				error_reporting(-1);
				ini_set('error_reporting', E_ALL);
			} else {
				error_reporting(0);
			}

			// Initialize global variables
			$currentTime = time();

			// Scan and delete
			deleteOlderItems();

			exit();

			function deleteOlderItems() {

				// Scans the directory for older items and deletes them

				global $directoryPath, $dayCount, $includeDirectories, $currentTime;

				$ignoredItems = ['.', '..'];

				$scan = scandir($directoryPath);

				foreach ($scan as $key => $itemName) {

					if (!in_array($itemName, $ignoredItems)) {

						$itemPath = $directoryPath.$itemName;
						$isDirectory = is_dir($itemPath);

						if ($includeDirectories || !$isDirectory) {

							$creationTime = filemtime($itemPath);

							$ageInDays = floor(($currentTime - $creationTime) / 60 / 60 / 24 );

							if ($ageInDays >= $dayCount) {

								if ($isDirectory) {

									if (deleteDirectory($itemPath)) {
										printStr('Deleted directory '.dayCountWithSuffix($ageInDays).' old: '.$itemPath);
									} else {
										printStr('Could not delete directory '.dayCountWithSuffix($ageInDays).' old: '.$itemPath);
									}

								} else {

									if (unlink($itemPath)) {
										printStr('Deleted file '.dayCountWithSuffix($ageInDays).' old: '.$itemPath);
									} else {
										printStr('Could not delete file '.dayCountWithSuffix($ageInDays).' old: '.$itemPath);
									}

								}

							}

						}

					}

				}

			}
			function deleteDirectory($directoryPath) {

				// Add directory separator if necessary
				if (!endsWith($directoryPath, DIRECTORY_SEPARATOR)) {
					$directoryPath .= DIRECTORY_SEPARATOR;
				}

				if (is_dir($directoryPath)) {

					$items = scandir($directoryPath);

					foreach ($items as $item) {

						if ($item != '.' && $item != '..') {

							$itemPath = $directoryPath.$item;

							if (is_dir($itemPath) && !is_link($itemPath)) {

								if (!deleteDirectory($itemPath)) {

									return false;

								}

							} else {

								if (!unlink($itemPath)) {

									return false;

								}

							}
						}
					}

					rmdir($directoryPath);

					return true;

				} else {

					return false;
				}

			}
			function initWithParameters($params) {

				// Initializes global variables from command line parameters

				global $directoryPath, $dayCount, $includeDirectories, $debugMode;

				if (!isset($params)) {

					// No parameters
					printUsage();

				} else if (count($params) < 3) {

					// Parameter count mismatch
					printUsage('This script requires at least two parameters. Please specify the directory path followed by the count of days for maximum file age.');

				} else {

					// Get and validate the first parameter as a directory path
					$directoryPath = $params->directory;
					if (!endsWith($directoryPath, '/')) {
						$directoryPath .= DIRECTORY_SEPARATOR;
					}
					if (!is_dir($directoryPath)) {
						printUsage('No directory found at '.$directoryPath.'. Please specify a valid directory path as the first parameter.');
					}

					// Get and validate the second parameter as an integer
					if ((string)(int)$params->days != $params->days) {
						printUsage('The second parameter is invalid. Please specify an integer for the count of days.');
					}
					$dayCount = (int)$params->days;

					// Determine parameter value to include directories or not
					$includeDirectories = false;
					if ($params->deletedirectories) {
						if ($params->deletedirectories == '--delete-directories') {
							$includeDirectories = true;
						} else {
							printUsage('Invalid parameter: '.$params[3]);
						}
					}


					$debugMode = false;
					$debugMode = $params->debugMode;
					if ($params->debugMode) {
						if ($params->debugMode == '1') {
							$debugMode = true;
						} else {
							$debugMode = false;
						}
					}


				}

			}

		}
		else
		{
			$this->addTaskLog(sprintf('Starting %d timeout', $timeout));
			sleep($timeout);
			$this->addTaskLog(sprintf('%d timeout over!', $timeout));
		}

		$this->taskEnd($event, 0);
	}

	/**
	 * @param   Event  $event  The onContentPrepareForm event.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since  __DEPLOY_VERSION__
	 */
	public function manipulateForms(Event $event): void
	{
		/** @var Form $form */
		$form = $event->getArgument('0');
		$data = $event->getArgument('1');

		$context = $form->getName();

		if ($context === 'com_scheduler.task')
		{
			$this->enhanceTaskItemForm($form, $data);
		}
	}

	/**
	 * @return void
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	private function stressMemory(): void
	{
		$mLimit = $this->getMemoryLimit();
		$this->addTaskLog(sprintf('Memory Limit: %d KB', $mLimit));

		$iMem = $cMem = memory_get_usage();
		$i = 0;

		while ($cMem + ($cMem - $iMem) / ++$i <= $mLimit)
		{
			$this->addTaskLog(sprintf('Current memory usage: %d KB', $cMem));
			${"array" . $i} = array_fill(0, 100000, 1);
		}
	}

	/**
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	private function stressMemoryRemoveLimit(): void
	{
		$success = false;

		if (function_exists('ini_set'))
		{
			$success = ini_set('memory_limit', -1) !== false;
		}

		$this->addTaskLog('Memory limit override ' . $success ? 'successful' : 'failed');
		$this->getMemoryLimit();
	}

	/**
	 * Processes the PHP ini memory_limit setting, returning the memory limit in KB
	 *
	 * @return float
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private function getMemoryLimit(): float
	{
		$memoryLimit = ini_get('memory_limit');

		if (preg_match('/^(\d+)(.)$/', $memoryLimit, $matches))
		{
			if ($matches[2] == 'M')
			{
				// * nnnM -> nnn MB
				$memoryLimit = $matches[1] * 1024 * 1024;
			}
			else
			{
				if ($matches[2] == 'K')
				{
					// * nnnK -> nnn KB
					$memoryLimit = $matches[1] * 1024;
				}
			}
		}

		return (float) $memoryLimit;
	}
}
