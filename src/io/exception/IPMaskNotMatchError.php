<?php


namespace sinri\ark\io\exception;


use Exception;
use Throwable;

/**
 * Class IPMaskNotMatchError
 * @package sinri\ark\io\exception
 * @since 3.4.2
 */
class IPMaskNotMatchError extends Exception
{
    /**
     * @var string
     */
    protected string $ip;
    /**
     * @var string
     */
    protected string $ipMask;

    public function __construct($ip, $ip_mask, $code = 0, Throwable $previous = null)
    {
        parent::__construct("IP [$ip] does not match mask [$ip_mask].", $code, $previous);
        $this->ip = $ip;
        $this->ipMask = $ip_mask;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @return string
     */
    public function getIpMask(): string
    {
        return $this->ipMask;
    }
}