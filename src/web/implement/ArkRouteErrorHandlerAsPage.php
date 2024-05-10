<?php


namespace sinri\ark\web\implement;


use Exception;
use sinri\ark\core\exception\ArkNestedException;
use sinri\ark\io\ArkWebOutput;
use sinri\ark\io\exception\TemplateFileNotFoundError;
use sinri\ark\web\ArkRouteErrorHandlerInterface;
use sinri\ark\web\exception\ArkWebRequestFailed;

class ArkRouteErrorHandlerAsPage implements ArkRouteErrorHandlerInterface
{
    /**
     * @var string
     */
    protected string $templateFile;

    /**
     * ArkRouteErrorHandlerAsPage constructor.
     * @param string $templateFile
     */
    public function __construct(string $templateFile = '')
    {
        $this->templateFile = $templateFile;
    }

    /**
     * Override this, if you need a special template file selector
     * @return string
     */
    public function getTemplateFile(): string
    {
        return $this->templateFile;
    }

    /**
     * @param Exception $error
     * @param int $http_code
     */
    public function execute(Exception $error, int $http_code): void
    {
        try {
            $templateFile = $this->getTemplateFile();
            if (!file_exists($templateFile)) {
                throw new TemplateFileNotFoundError($templateFile);
            }
            $parameters['exception'] = $error;
            if (is_a($error, ArkWebRequestFailed::class)) {
                $parameters['detail'] = $error->getDetail();
            }
            if (is_a($error, ArkNestedException::class)) {
                $parameters['nested'] = $error->getNestedMessage();
            }
            ArkWebOutput::getSharedInstance()
                ->sendHTTPCode($http_code)
                ->displayPage($templateFile, $parameters);
        } catch (TemplateFileNotFoundError $exception) {
            echo $exception->getMessage() . PHP_EOL . $exception->getTraceAsString();
        }
    }
}