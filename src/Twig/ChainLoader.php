<?php

namespace Bolt\Twig;

use Twig_Error_Loader as LoaderError;
use Twig_ExistsLoaderInterface as ExistsLoaderInterface;
use Twig_LoaderInterface as LoaderInterface;
use Twig_Source as Source;
use Twig_SourceContextLoaderInterface as SourceContextLoaderInterface;

/**
 * Loads templates from other loaders.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Carson Full <carsonfull@gmail.com>
 */
class ChainLoader implements LoaderInterface, ExistsLoaderInterface, SourceContextLoaderInterface, ListLoaderInterface
{
    /** @var array [string]: bool */
    private $hasSourceCache = [];
    /** @var LoaderInterface[] */
    protected $loaders = [];

    /**
     * Constructor.
     *
     * @param LoaderInterface[] $loaders
     */
    public function __construct(array $loaders = [])
    {
        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }
    }

    /**
     * Add loader.
     *
     * @param LoaderInterface $loader
     */
    public function addLoader(LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
        $this->hasSourceCache = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getSource($name)
    {
        @trigger_error(sprintf('Calling "getSource" on "%s" is deprecated since 1.27. Use getSourceContext() instead.', get_class($this)), E_USER_DEPRECATED);

        $exceptions = [];
        foreach ($this->loaders as $loader) {
            if ($loader instanceof ExistsLoaderInterface && !$loader->exists($name)) {
                continue;
            }

            try {
                return $loader->getSource($name);
            } catch (LoaderError $e) {
                $exceptions[] = $e->getMessage();
            }
        }

        throw new LoaderError(sprintf('Template "%s" is not defined%s.', $name, $exceptions ? ' ('.implode(', ', $exceptions).')' : ''));
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceContext($name)
    {
        $exceptions = [];
        foreach ($this->loaders as $loader) {
            if ($loader instanceof ExistsLoaderInterface && !$loader->exists($name)) {
                continue;
            }

            try {
                if ($loader instanceof SourceContextLoaderInterface) {
                    return $loader->getSourceContext($name);
                }

                return new Source($loader->getSource($name), $name);
            } catch (LoaderError $e) {
                $exceptions[] = $e->getMessage();
            }
        }

        throw new LoaderError(sprintf('Template "%s" is not defined%s.', $name, $exceptions ? ' ('.implode(', ', $exceptions).')' : ''));
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        $name = (string) $name;

        if (isset($this->hasSourceCache[$name])) {
            return $this->hasSourceCache[$name];
        }

        foreach ($this->loaders as $loader) {
            if ($loader instanceof ExistsLoaderInterface) {
                if ($loader->exists($name)) {
                    return $this->hasSourceCache[$name] = true;
                }

                continue;
            }

            try {
                if ($loader instanceof SourceContextLoaderInterface) {
                    $loader->getSourceContext($name);
                } else {
                    $loader->getSource($name);
                }

                return $this->hasSourceCache[$name] = true;
            } catch (LoaderError $e) {
            }
        }

        return $this->hasSourceCache[$name] = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey($name)
    {
        $exceptions = [];
        foreach ($this->loaders as $loader) {
            if ($loader instanceof ExistsLoaderInterface && !$loader->exists($name)) {
                continue;
            }

            try {
                return $loader->getCacheKey($name);
            } catch (LoaderError $e) {
                $exceptions[] = get_class($loader).': '.$e->getMessage();
            }
        }

        throw new LoaderError(sprintf('Template "%s" is not defined%s.', $name, $exceptions ? ' ('.implode(', ', $exceptions).')' : ''));
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($name, $time)
    {
        $exceptions = [];
        foreach ($this->loaders as $loader) {
            if ($loader instanceof ExistsLoaderInterface && !$loader->exists($name)) {
                continue;
            }

            try {
                return $loader->isFresh($name, $time);
            } catch (LoaderError $e) {
                $exceptions[] = get_class($loader).': '.$e->getMessage();
            }
        }

        throw new LoaderError(sprintf('Template "%s" is not defined%s.', $name, $exceptions ? ' ('.implode(', ', $exceptions).')' : ''));
    }

    /**
     * {@inheritdoc}
     */
    public function listTemplates()
    {
        foreach ($this->loaders as $loader) {
            if (!$loader instanceof ListLoaderInterface) {
                continue;
            }

            foreach ($loader->listTemplates() as $source) {
                yield $source;
            }
        }
    }
}
