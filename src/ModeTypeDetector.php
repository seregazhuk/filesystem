<?php

namespace React\Filesystem;

use React\Filesystem\FilesystemInterface;
use React\Filesystem\TypeDetectorInterface;
use React\Promise\RejectedPromise;

class ModeTypeDetector implements TypeDetectorInterface
{
    /**
     * @var array
     */
    protected $mapping = [
        0xa000 => 'constructLink',
        0x4000 => 'dir',
        0x8000 => 'file',
    ];

    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @param FilesystemInterface $filesystem
     */
    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param array $node
     * @return \React\Promise\PromiseInterface
     */
    public function detect(array $node)
    {
        return $this->filesystem->getAdapter()->stat($node['path'])->then(function ($stat) {
            return $this->walkMapping($stat);
        });
    }

    protected function walkMapping($stat)
    {
        $promiseChain = new RejectedPromise();
        foreach ($this->mapping as $mappingMode => $method) {
            $promiseChain = $promiseChain->otherwise(function () use ($stat, $mappingMode, $method) {
                return $this->matchMapping($stat['mode'], $mappingMode, $method);
            });
        }
        return $promiseChain;
    }

    protected function matchMapping($mode, $mappingMode, $method)
    {
        if (($mode & $mappingMode) == $mappingMode) {
            return \React\Promise\resolve([
                $this->filesystem,
                $method,
            ]);
        }

        return new RejectedPromise();
    }
}
