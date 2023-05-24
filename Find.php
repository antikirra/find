<?php

namespace Antikirra\Find;

final class Find
{
    /**
     * @var \RecursiveDirectoryIterator
     */
    private $iterator;

    /**
     * @var ?int
     */
    private $softLimit = null;

    /**
     * @var ?int
     */
    private $hardLimit = null;

    /**
     * @var ?bool
     */
    private $filesOnly = null;

    /**
     * @var ?bool
     */
    private $directoriesOnly = null;

    /**
     * @var array
     */
    private $extensions = [];

    /**
     * @param string $directory
     */
    private function __construct($directory)
    {
        if (!is_dir($directory) || !is_readable($directory)) {
            throw new \RuntimeException();
        }

        $this->iterator = new \RecursiveDirectoryIterator($directory, \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS);
    }

    /**
     * @param string $directory
     */
    public static function in($directory)
    {
        return new self($directory);
    }

    /**
     * @param int $limit
     * @return self
     */
    public function withSoftLimit($limit)
    {
        if ($limit <= 0) {
            throw new \InvalidArgumentException();
        }

        if ($this->hardLimit && $limit > $this->hardLimit) {
            throw new \InvalidArgumentException();
        }

        $this->softLimit = $limit;

        return $this;
    }

    /**
     * @param int $limit
     * @return self
     */
    public function withHardLimit($limit)
    {
        if ($limit <= 0) {
            throw new \InvalidArgumentException();
        }

        if ($this->softLimit && $limit < $this->softLimit) {
            throw new \InvalidArgumentException();
        }

        $this->hardLimit = $limit;

        return $this;
    }

    /**
     * @return self
     */
    public function filesOnly()
    {
        if (true === $this->directoriesOnly) {
            throw new \InvalidArgumentException();
        }

        $this->filesOnly = true;

        return $this;
    }

    /**
     * @return self
     */
    public function directoriesOnly()
    {
        if (true === $this->filesOnly) {
            throw new \InvalidArgumentException();
        }

        $this->directoriesOnly = true;

        return $this;
    }

    public function withExtensions(array $extensions)
    {
        if (true === $this->directoriesOnly) {
            throw new \InvalidArgumentException();
        }

        $extensions = array_filter($extensions);
        $extensions = array_map('strtolower', $extensions);
        $this->extensions = array_unique(array_merge($this->extensions, $extensions));

        return $this;
    }

    /**
     * @param ?\Closure $closure
     * @return \Generator
     */
    public function find(\Closure $closure = null)
    {
        $iteration = 0;
        $success = 0;

        /**
         * @var \SplFileInfo $fileInfo
         */
        foreach (new \RecursiveIteratorIterator($this->iterator, \RecursiveIteratorIterator::LEAVES_ONLY, \RecursiveIteratorIterator::CATCH_GET_CHILD) as $fileInfo) {
            ++$iteration;

            if (true === $this->filesOnly && !$fileInfo->isFile()) {
                continue;
            }

            if (true === $this->directoriesOnly && !$fileInfo->isDir()) {
                continue;
            }

            if (!empty($this->extensions) && $fileInfo->isFile() && !in_array(strtolower($fileInfo->getExtension()), $this->extensions, true)) {
                continue;
            }

            $result = $closure instanceof \Closure ? $closure($fileInfo) : $fileInfo;

            if ($result) {
                ++$success;
                yield $fileInfo->getRealPath() => $result;
                if (null !== $this->softLimit && $success >= $this->softLimit) {
                    break;
                }
            }

            if (null !== $this->hardLimit && $iteration >= $this->hardLimit) {
                break;
            }
        }
    }
}
