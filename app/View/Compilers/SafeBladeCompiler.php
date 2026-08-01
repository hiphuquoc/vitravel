<?php

namespace App\View\Compilers;

use Illuminate\View\Compilers\BladeCompiler;
use Throwable;

/**
 * WSL/multi-user: touch($path, $mtime) fails when PHP-FPM (www-data)
 * is not the owner of a compiled view. Fall back to rewriting the file.
 */
class SafeBladeCompiler extends BladeCompiler
{
    public function compile($path = null): void
    {
        if ($path) {
            $this->setPath($path);
        }

        if (is_null($this->cachePath)) {
            return;
        }

        $contents = $this->compileString($this->files->get($this->getPath()));

        if (! empty($this->getPath())) {
            $contents = $this->appendFilePath($contents);
        }

        $this->ensureCompiledDirectoryExists(
            $compiledPath = $this->getCompiledPath($this->getPath())
        );

        if (! $this->files->exists($compiledPath)) {
            $this->files->replace($compiledPath, $contents);

            return;
        }

        $compiledHash = $this->files->hash($compiledPath, 'xxh128');

        if ($compiledHash !== hash('xxh128', $contents)) {
            $this->files->replace($compiledPath, $contents);

            return;
        }

        $lastModified = $this->files->lastModified($this->getPath());

        if ($lastModified >= $this->files->lastModified($compiledPath)) {
            try {
                touch($compiledPath, $lastModified + 1);
            } catch (Throwable) {
                // Non-owner cannot set arbitrary mtime — rewrite to refresh timestamp.
                $this->files->replace($compiledPath, $contents);
            }
        }
    }
}
