<?php


namespace sinri\ark\web\implement;


use Exception;
use sinri\ark\io\ArkWebOutput;
use sinri\ark\io\exception\TemplateFileNotFoundError;
use sinri\ark\web\ArkRouteErrorHandlerInterface;

class ArkRouteErrorHandlerAsPage implements ArkRouteErrorHandlerInterface
{
    /**
     * @var string
     */
    protected $templateFile;

    /**
     * ArkRouteErrorHandlerAsPage constructor.
     * @param string $templateFile
     */
    public function __construct($templateFile = '')
    {
        $this->templateFile = $templateFile;
    }

    /**
     * Override this, if you need a special template file selector
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->templateFile;
    }

    /**
     * @param array|Exception $error
     * @param int $http_code
     */
    public function execute($error, $http_code = 404)
    {
        try {
            $templateFile = $this->getTemplateFile();
            if (!is_string($templateFile) || !file_exists($templateFile)) {
                throw new TemplateFileNotFoundError($templateFile);
            }
            $parameters = [];
            if (is_array($error)) {
                $parameters = $error;
            } elseif (is_a($error, Exception::class)) {
                $parameters['exception'] = $error;
            }
            ArkWebOutput::getSharedInstance()
                ->sendHTTPCode($http_code)
                ->displayPage($templateFile, $parameters);
        } catch (TemplateFileNotFoundError $exception) {
            echo $exception->getMessage() . PHP_EOL . $exception->getTraceAsString();
        }
    }
}