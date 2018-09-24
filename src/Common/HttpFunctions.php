<?php
namespace HTR\Common;

use App\System\Configuration as cfg;

class HttpFunctions
{

    /**
     * Set header Access-Control-Allow-Origin to Allowed hosts
     */
    public static function allowCors()
    {
        $origin = filter_input(INPUT_SERVER, 'HTTP_ORIGIN');
        $protocol = filter_input(INPUT_SERVER, 'REQUEST_SCHEME') ?? 'http';
        $host = preg_replace("/^http(s)?:\/{2}/", "", $origin);
        $allowedHosts = cfg::ALLOW_CORS;
        $allowedHosts[] = cfg::HOST_DEV;

        if (in_array($host, $allowedHosts)) {
            header("Access-Control-Allow-Origin: " . $protocol . "://" . $host);
        }
    }

    /**
     * Set header Access-Control-Allow-Headers to Allowed headers
     */
    public static function allowHeader()
    {
        if (count(cfg::ALLOW_HEADERS)) {
            header("Access-Control-Allow-Headers:" . implode(', ', cfg::ALLOW_HEADERS));
        }
    }
}
