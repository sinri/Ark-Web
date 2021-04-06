<?php


namespace sinri\ark\web\implement;


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

    public function execute($errorData = [], $http_code = 404)
    {
        try {
            $templateFile = $this->getTemplateFile();
            if (!is_string($templateFile) || !file_exists($templateFile)) {
                throw new TemplateFileNotFoundError($templateFile);
            }
            ArkWebOutput::getSharedInstance()
                ->sendHTTPCode($http_code)
                ->displayPage($templateFile, $errorData);
        } catch (TemplateFileNotFoundError $exception) {
            echo $exception->getMessage() . PHP_EOL . $exception->getTraceAsString();
        }
    }
}