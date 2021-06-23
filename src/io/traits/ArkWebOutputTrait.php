<?php


namespace sinri\ark\io\traits;


use Mimey\MimeTypes;
use sinri\ark\io\ArkWebOutput;
use sinri\ark\io\exception\TargetFileNotFoundError;

/**
 * Trait ArkWebOutputTrait
 * @package sinri\ark\io\traits
 * @since 3.4.2
 */
trait ArkWebOutputTrait
{
    /**
     * @var callable such as function($anythingToBeEncoded,$errorMessage,$errorCode)
     * @since 3.4.0
     */
    protected $jsonEncodeErrorHandler;
    /**
     * @var callable such as function($templatePath,$params)
     * @since 3.4.0
     */
    protected $displayPageTemplateMissingHandler;

    /**
     * @return callable
     * @since 3.4.0
     */
    public function getDisplayPageTemplateMissingHandler()
    {
        return $this->displayPageTemplateMissingHandler;
    }

    /**
     * @param callable $displayPageTemplateMissingHandler such as function($templatePath,$params)
     * @return ArkWebOutputTrait
     * @since 3.4.0
     */
    public function setDisplayPageTemplateMissingHandler(callable $displayPageTemplateMissingHandler)
    {
        $this->displayPageTemplateMissingHandler = $displayPageTemplateMissingHandler;
        return $this;
    }

    /**
     * @return callable
     * @since 3.4.0
     */
    public function getJsonEncodeErrorHandler()
    {
        return $this->jsonEncodeErrorHandler;
    }

    /**
     * @param callable $jsonEncodeErrorHandler such as function($anythingToBeEncoded,$errorMessage,$errorCode)
     * @return ArkWebOutputTrait
     * @since 3.4.0
     */
    public function setJsonEncodeErrorHandler(callable $jsonEncodeErrorHandler)
    {
        $this->jsonEncodeErrorHandler = $jsonEncodeErrorHandler;
        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentHTTPCode()
    {
        return http_response_code();
    }

    /**
     * @param string $contentType
     * @param null|string $charSet
     * @return ArkWebOutputTrait
     * @since 2.8.1 return $this
     */
    public function setContentTypeHeader($contentType, $charSet = null)
    {
        $this->sendHeader("Content-Type: " . $contentType . ($charSet !== null ? '; charset=' . $charSet : ''));
        return $this;
    }

    /**
     * @param string $header
     * @param bool $replace
     * @return $this
     */
    public function sendHeader(string $header, $replace = true)
    {
        header($header, $replace);
        return $this;
    }

    /**
     * @param string $code OK or FAIL
     * @param mixed $data
     * @param int $options @since 3.2.2
     * @param int $depth @since 3.2.2
     * @param mixed $debugInfo @since 3.4.7
     * Exception thrown @since 3.2.2 and could be handled @since 3.4.0
     */
    public function jsonForAjax($code = ArkWebOutput::AJAX_JSON_CODE_OK, $data = '', $options = 0, $depth = 512, $debugInfo = null)
    {
        $x = ["code" => $code, "data" => $data];
        if ($debugInfo !== null) {
            $x['debug_info'] = $debugInfo;
        }
        $this->json($x, $options, $depth);
    }

    /**
     * @param mixed $anything
     * @param int $options
     * @param int $depth
     * Exception thrown @since 3.2.2 and could be handled @since 3.4.0
     */
    public function json($anything, $options = 0, $depth = 512)
    {
        $response = json_encode($anything, $options, $depth);
        if ($response === false) {
            //throw new Exception("JSON ENCODING FAILED: " . json_last_error_msg());
            if (is_callable($this->jsonEncodeErrorHandler)) {
                call_user_func_array($this->jsonEncodeErrorHandler, [$anything, json_last_error_msg(), json_last_error()]);
            } else {
                echo "JSON ENCODING FAILED: " . json_last_error_msg() . PHP_EOL;
                echo "Target Content to encode: " . PHP_EOL;
                print_r($anything);
            }
        } else {
            echo $response;
        }
    }

    /**
     * @param string $templateFile
     * @param array $params
     * Exception thrown @since 3.2.2 and could be handled @since 3.4.0
     */
    public function displayPage($templateFile, $params = [])
    {
        if (!file_exists($templateFile)) {
            if (is_callable($this->displayPageTemplateMissingHandler)) {
                call_user_func_array($this->displayPageTemplateMissingHandler, [$templateFile, $params]);
            } else {
                echo 'Template is missing';
            }
            //throw new Exception("Template file [{$templateFile}] not found.");
            return;
        }
        extract($params);
        /** @noinspection PhpIncludeInspection */
        require $templateFile;
    }

    /**
     * @param string $url
     * @since 2.8.1
     * @since 3.1.7 remove urlencode on url
     */
    public function redirect($url)
    {
        $this->sendHTTPCode(302);
        header("Location: " . $url);
    }

    /**
     * @param int $httpCode
     * @return ArkWebOutputTrait
     * @since 2.8.1 return $this instead of int
     */
    public function sendHTTPCode(int $httpCode)
    {
        http_response_code($httpCode);
        return $this;
    }

    /**
     * 文件通过非直接方式下载
     * @param string $file
     * @param null $down_name Extension Free File Name For Download
     * @param null|string $content_type
     * @return bool
     * @throws TargetFileNotFoundError
     */
    public function downloadFileIndirectly($file, $content_type = null, $down_name = null)
    {
        if (!file_exists($file)) {
            throw new TargetFileNotFoundError($file);
        }

        if ($down_name !== null && $down_name !== false) {
            $extension = substr($file, strrpos($file, '.')); //获取文件后缀
            $down_name = $down_name . $extension; //新文件名，就是下载后的名字
        } else {
            $k = pathinfo($file);
            $extension = $k['extension'];
            $down_name = $k['filename'] . '.' . $extension;
        }

        $fp = fopen($file, "r");
        $file_size = filesize($file);

        if ($content_type === null) {
            // @since 1.5.0 The default $content_type for NULL would not be ArkWebOutput::CONTENT_TYPE_OCTET_STREAM any more
            // but use MimeTypes to parse from extension.
            $content_type = $this->getMimeTypeByExtension($extension);
        }
        if ($content_type === ArkWebOutput::CONTENT_TYPE_OCTET_STREAM) {
            $content_disposition = 'attachment; filename=' . $down_name;
        } else {
            $content_disposition = 'inline';
        }

        // Headers
        header("Content-Type: " . $content_type);
        //header("Accept-Ranges: bytes");
        header("Content-Length: " . $file_size);
        header("Content-Disposition: " . $content_disposition);
        $bufferSize = 1024;
        $fileSentBytesCount = 0;

        // Write to client
        while (!feof($fp) && $fileSentBytesCount < $file_size) {
            $buffer = fread($fp, $bufferSize);
            $fileSentBytesCount += $bufferSize;
            echo $buffer;
            /**
             * This flush added @since 2.3 try to make the save dialog comes soon
             */
            flush();
        }
        fclose($fp);
        return true;
    }

    /**
     * @param string $extension
     * @return string
     * @since 1.5.0
     */
    public function getMimeTypeByExtension($extension)
    {
        $mime = (new MimeTypes())->getMimeType($extension);
        if ($mime === null) $mime = ArkWebOutput::CONTENT_TYPE_OCTET_STREAM;
        return $mime;
    }

    /**
     * 这是一个实验性的功能，需要在NGINX设定好internal声明并且计划好对应的文件库。
     * @param string $baseUrl e.g. /protected (set in nginx config)
     * @param string $baseDirectory e.g. /var/code/protected_file
     * @param string $relativeFilePath e.g. sub_path/file.ext
     * @param null|string $down_name e.g. customized.name
     * @return bool
     * @throws TargetFileNotFoundError
     * @notice 依赖外部组件，用于生产环境时需要实地验证。
     * @since 2.9.0
     */
    public function downloadFileThroughNginxSendFile($baseUrl, $baseDirectory, $relativeFilePath, $down_name = null)
    {
        /**
         * here base url is /protected
         * # IN NGINX server block 这个是定义读取你的文件的目录的url开头  直接访问是不可以的
         * location /protected {
         *  internal;
         *  alias   /var/vhost/demo/upload_files;
         * }
         */

        $file = $baseDirectory . DIRECTORY_SEPARATOR . $relativeFilePath;

        if (!file_exists($file)) {
            throw new TargetFileNotFoundError($file);
        }

        if ($down_name !== null && $down_name !== false) {
            $extension = substr($file, strrpos($file, '.')); //获取文件后缀
            $down_name = $down_name . $extension; //新文件名，就是下载后的名字
        } else {
            $k = pathinfo($file);
            $extension = $k['extension'];
            $down_name = $k['filename'] . '.' . $extension;
        }

        $content_type = $this->getMimeTypeByExtension($extension);
        if ($content_type === ArkWebOutput::CONTENT_TYPE_OCTET_STREAM) {
            //$content_disposition = 'attachment; filename=' . $down_name;

            // 处理中文文件名
            $ua = $_SERVER ["HTTP_USER_AGENT"];
            if (preg_match("/MSIE/", $ua)) {
                //$encoded_filename = rawurlencode ( $down_name );
                header('Content-Disposition: attachment; filename="' . rawurlencode($down_name) . '"');
            } else if (preg_match("/Firefox/", $ua)) {
                header("Content-Disposition: attachment; filename*=\"utf8''" . $down_name . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . $down_name . '"');
            }
        } else {
            // $content_disposition = 'inline';
            header('Content-Disposition: inline');
        }

        // 就这么简单一句话搞定 注意“protected”是和nginx配置文件的 protected要一致
        header("X-Accel-Redirect: " . $baseUrl . "/" . $relativeFilePath);

        return true;
    }
}