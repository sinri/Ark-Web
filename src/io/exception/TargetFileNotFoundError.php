<?php


namespace sinri\ark\io\exception;


use Exception;
use Throwable;

/**
 * Class TargetFileNotFoundError
 * @package sinri\ark\io\exception
 * @since 3.4.2
 */
class TargetFileNotFoundError extends Exception
{
    /**
     * @var string
     */
    protected $targetFile;

    public function __construct(string $file, Throwable $previous = null)
    {
        parent::__construct("File [$file] is not there", 404, $previous);
        $this->targetFile = $file;
    }

    /**
     * @return string
     */
    public function getTargetFile(): string
    {
        return $this->targetFile;
    }
}