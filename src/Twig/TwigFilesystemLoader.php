<?php

namespace Bolt\Twig;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Twig_Loader_Filesystem as FilesystemLoader;
use Twig_Source as Source;

class TwigFilesystemLoader extends FilesystemLoader implements ListLoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function listTemplates()
    {
        foreach ($this->paths as $namespace => $paths) {
            $namespace = $namespace === static::MAIN_NAMESPACE ? '' : '@' . $namespace . '/';

            foreach ($paths as $path) {
                $finder = (new Finder())
                    ->files()
                    ->in($path)
                    ->depth('<4')
                    ->name('*.twig')
                    ->notName('/^_/')
                    ->notPath('node_modules')
                    ->notPath('bower_components')
                ;
                foreach ($finder as $file) {
                    /** @var SplFileInfo $file */
                    $source = new Source('', $namespace . $file->getRelativePathname(), $file->getPathname());

                    yield $source;
                }
            }
        }
    }
}
