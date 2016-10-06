<?php

namespace Bolt;

use Bolt\Configuration\Composer;
use Bolt\Configuration\ResourceManager;
use Bolt\Configuration\Standard;
use Bolt\Debug\ShutdownHandler;
use Bolt\Exception\BootException;
use Silex;
use Symfony\Component\Yaml\Yaml;

final class Bootstrap
{
    private $root;
    private $resourcesClass;
    private $config;

    /**
     * @return \Symfony\Component\Console\Application
     */
    public static function nut()
    {
        $app = static::run();

        return $app['nut'];
    }

    /**
     * @return Silex\Application|false
     */
    public static function web()
    {
        $app = static::run();

        if (PHP_SAPI === 'cli-server') {
            if (is_file($_SERVER['DOCUMENT_ROOT'] . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']))) {
                return false;
            }

            // Fix server variables for PHP built-in server so base path is correctly determined.
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $frame = end($trace);
            // Absolute path to entry file
            $_SERVER['SCRIPT_FILENAME'] = $frame['file'];
            // Relative path to entry file from document root (dir the server is point to)
            $_SERVER['SCRIPT_NAME'] = preg_replace("#^{$_SERVER['DOCUMENT_ROOT']}#", '', $_SERVER['SCRIPT_FILENAME']);
        }

        return $app;
    }

    public static function run()
    {
        $bootstrap = new static();

        // Use UTF-8 for all multi-byte functions
        mb_internal_encoding('UTF-8');
        mb_http_output('UTF-8');

        $bootstrap->loadAutoloader();

        // Register handlers early
        ShutdownHandler::register();

        $bootstrap->loadConfig();

        $app = $bootstrap->createApp();

        return $app;
    }

    private function loadAutoloader()
    {
        // Resolve Bolt-root
        $boltRootPath = realpath(__DIR__ . '/..');

        // Look for the autoloader in known positions relative to the Bolt-root,
        // and autodetect an appropriate configuration class based on this
        // information. (autoload.php path maps to a configuration class)
        $autodetectionMappings = [
            $boltRootPath . '/vendor/autoload.php' => [Standard::class, $boltRootPath],
            $boltRootPath . '/../../autoload.php'  => [Composer::class, $boltRootPath . '/../../..'],
        ];

        foreach ($autodetectionMappings as $autoloadPath => list($resourcesClass, $rootPath)) {
            if (!file_exists($autoloadPath)) {
                continue;
            }

            $this->root = $rootPath;
            $this->resourcesClass = $resourcesClass;

            /** @noinspection PhpIncludeInspection */
            require_once $autoloadPath;

            return;
        }

        // None of the mappings matched, error
        require_once $boltRootPath . '/src/Exception/BootException.php';
        BootException::earlyExceptionComposer();
    }

    /**
     * Load initialization config needed to bootstrap application.
     *
     * In order for paths to be customized and still have the standard
     * index.php (web) and nut (CLI) work, there needs to be a standard
     * place these are defined. This is ".bolt.yml" or ".bolt.php" in the
     * project root (determined above).
     *
     * Yes, YAML and PHP are supported here (not both). YAML works for
     * simple values and PHP supports any programmatic logic if required.
     */
    private function loadConfig()
    {
        $this->config = [
            'application' => null,
            'resources'   => null,
            'paths'       => [],
        ];

        if (file_exists($this->root . '/.bolt.yml')) {
            $yaml = Yaml::parse(file_get_contents($this->root . '/.bolt.yml')) ?: [];
            $this->config = array_replace_recursive($this->config, $yaml);
        } elseif (file_exists($this->root . '/bolt.yml')) {
            $yaml = Yaml::parse(file_get_contents($this->root . '/bolt.yml')) ?: [];
            $this->config = array_replace_recursive($config, $yaml);
        } elseif (file_exists($this->root . '/.bolt.php')) {
            $php = include $this->root . '/.bolt.php';
        } elseif (file_exists($this->root . '/bolt.php')) {
            $php = include $this->root . '/bolt.php';
        }

        if (isset($php) && is_array($php)) {
            $this->config = array_replace_recursive($this->config, $php);
        } elseif (isset($php) && $php instanceof Silex\Application) {
            $this->config['application'] = $php;
        }
    }

    private function createApp()
    {
        $appClass = $this->config['application'];

        // If application object is provided, assume it is ready to go.
        if ($appClass instanceof Silex\Application) {
            return $appClass;
        }

        // Use resources from config, or instantiate the class based on mapping above.
        if ($this->config['resources'] instanceof ResourceManager) {
            $resources = $this->config['resources'];
        } else {
            $resourcesClass = $this->resourcesClass;
            if ($this->config['resources'] !== null && is_a($this->config['resources'], ResourceManager::class, true)) {
                $resourcesClass = $this->config['resources'];
            }

            /** @var \Bolt\Configuration\ResourceManager $resources */
            $resources = new $resourcesClass($this->root);
        }

        // Set any non-standard paths
        foreach ((array) $this->config['paths'] as $name => $path) {
            $resources->setPath($name, $path);
        }

        if ($appClass === null || !is_a($appClass, Silex\Application::class, true)) {
            $appClass = Application::class;
        }

        /** @var Silex\Application $app */
        $app = new $appClass(['resources' => $resources]);

        // Initialize the 'Bolt application': Set up all routes, providers, database, templating, etc..
        if (method_exists($app, 'initialize')) {
            $app->initialize();
        }

        return $app;
    }
}
