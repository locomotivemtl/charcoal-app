<?php

namespace Charcoal\App\Script;

use InvalidArgumentException;

/**
 * Path-handling utilities
 *
 * Requirements:
 * - `DIRECTORY_SEPARATORS` class constant.
 * - `DEFAULT_BASENAME` class constant.
 * - `self::parseAsArray()`
 */
trait PathScriptTrait
{
    /**
     * The cache of globbed paths.
     *
     * @var array
     */
    protected static $globCache = [];

    /**
     * Retrieve the base path.
     *
     * @return string|null
     */
    abstract public function basePath();

    /**
     * Process multiple paths.
     *
     * @param  string|string[] $paths One or many paths to scan.
     * @throws InvalidArgumentException If the paths are invalid.
     * @return string[]
     */
    public function processMultiplePaths($paths)
    {
        $paths = $this->parseAsArray($paths);
        $paths = array_map([ $this, 'filterPath' ], $paths);
        $paths = array_filter($paths, [ $this, 'pathExists' ]);

        if ($paths === false) {
            throw new InvalidArgumentException('Received invalid paths.');
        }

        if (empty($paths)) {
            throw new InvalidArgumentException('Received empty paths.');
        }

        return $paths;
    }

    /**
     * Determine if the path exists.
     *
     * @param  string $path Path to the file or directory.
     * @throws InvalidArgumentException If the path is invalid.
     * @return boolean Returns TRUE if the path exists.
     */
    public function pathExists($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('The path must be a string.');
        }

        return $this->globRecursive($this->basePath().'/'.$path, GLOB_BRACE);
    }

    /**
     * Filter the given path.
     *
     * Trims leading and trailing directory paths
     *
     * @param  string      $path Path to the file or directory.
     * @param  string|null $trim The characters to strip from the $path.
     * @throws InvalidArgumentException If the path is invalid.
     * @return string Returns the filtered path.
     */
    public function filterPath($path, $trim = null)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('The path must be a string.');
        }

        if ($trim === null && defined(get_called_class().'::DIRECTORY_SEPARATORS')) {
            $trim = static::DIRECTORY_SEPARATORS;
        }

        if ($trim) {
            if (!is_string($trim)) {
                throw new InvalidArgumentException(
                    'The characters to strip must be a string or use static::DIRECTORY_SEPARATORS.'
                );
            }

            $path = trim($path, $trim);
        }

        return trim($path);
    }

    /**
     * Filter the given path to be writable.
     *
     * @param  string      $path A writable path to a file or directory.
     * @param  string|null $name The target file name.
     * @throws InvalidArgumentException If the path is invalid.
     * @return string Returns the filtered path.
     */
    public function filterWritablePath($path, $name = null)
    {
        if ($name === null && defined(get_called_class().'::DEFAULT_BASENAME')) {
            $name = static::DEFAULT_BASENAME;
        }

        if (!is_string($name)) {
            throw new InvalidArgumentException(
                'The target file name must be a string or use static::DEFAULT_BASENAME.'
            );
        }

        $path = $this->filterPath($path);
        $test = $this->basePath().'/'.$path;

        if (is_dir($test)) {
            if (is_writable($test)) {
                $path .= '/'.$name;
            } else {
                throw new InvalidArgumentException('The target path is not writeable.');
            }
        } elseif (is_file($test)) {
            if (!is_writable($test)) {
                throw new InvalidArgumentException('The target file is not writeable.');
            }
        } else {
            $info = pathinfo($path);
            $path = $this->filterWritablePath($info['dirname'], $info['basename']);
        }

        return $path;
    }

    /**
     * Recursively find pathnames matching a pattern.
     *
     * @see    http://in.php.net/manual/en/function.glob.php#106595
     * @param  string  $pattern The search pattern.
     * @param  integer $flags   Bitmask of {@see glob()} options.
     * @return array
     */
    public function globRecursive($pattern, $flags = 0)
    {
        $maxDepth = $this->maxDepth();
        $depthKey = strval($maxDepth);

        if (isset(static::$globCache[$pattern][$depthKey])) {
            return static::$globCache[$pattern][$depthKey];
        }

        $depth = 1;
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).'/*', (GLOB_ONLYDIR|GLOB_NOSORT)) as $dir) {
            $files = array_merge($files, $this->globRecursive($dir.'/'.basename($pattern), $flags));
            $depth++;
            if ($maxDepth > 0 && $depth >= $maxDepth) {
                break;
            }
        }

        static::$globCache[$pattern][$depthKey] = array_filter($files, 'is_file');

        return $files;
    }
}
