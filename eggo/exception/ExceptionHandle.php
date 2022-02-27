<?php
declare(strict_types=1);

namespace Eggo\exception;

use Exception;

class ExceptionHandle
{
    private static $instance = null;

    /**
     * @param string $message
     * @param int $statusCode
     * @param bool $debug
     * @throws Exception
     */
    final private function __construct(string $message,
                                       int    $statusCode = 500,
                                       bool   $debug = false)
    {
        http_response_code($statusCode);
        if ($debug) {
            throw new Exception($message, $statusCode);
        }
        die(sprintf('ErrorCode=>%d: %s', $statusCode, $message));
    }

    /**
     * @throws Exception
     */
    final public static function Handle(string $message,
                                        int    $statusCode = 500,
                                        bool   $debug = false): ?ExceptionHandle
    {
        if (null === self::$instance) {
            self::$instance = new static(
                $message, $statusCode, $debug
            );
        }
        return self::$instance;
    }

}
