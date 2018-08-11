<?php

use App\System\Configuration as cfg;
use Slim\Http\Response;
use Slim\Http\Request;
use App\Helpers\ValidateAuthentication;

/**
 * Verify if is DEV mode is run
 * For set dev mode, is necessary the .devmode file in root directory of project
 *
 * @return bool
 */
function isDevMode(): bool
{
    $path = preg_replace("/\/public_fordev$/", "/.devmode", getcwd() . "_fordev");
    return file_exists($path);
}

/**
 * Set header Access-Control-Allow-Origin to Allowed hosts
 */
function allowCors()
{
    $origin = filter_input(INPUT_SERVER, 'HTTP_ORIGIN');
    $protocol = filter_input(INPUT_SERVER, 'REQUEST_SCHEME');
    $host = preg_replace("/^http(s)?:\/{2}/", "", $origin);
    $allowedHosts = cfg::ALLOW_CORS;
    $allowedHosts[] = cfg::HOST_DEV;

    if (in_array($host, $allowedHosts) || true) {
        header("Access-Control-Allow-Origin: " . $protocol . "://" . $host);
    }
}

/**
 * Check access level of user
 *
 * @param mixed $allowedLevel
 * @param Request $request
 * @param Response $response
 * @return array
 */
function allowAccessOnly($allowedLevel, Request $request, Response $response): array
{
    $dataUser = ValidateAuthentication::token($request);
    $userLevel = $dataUser->data->level;

    if (is_int($allowedLevel) && $userLevel != $allowedLevel) {
        return [
            "allowed" => false,
            "return" => $response->withJson([
                "message" => "Invalid User",
                "status" => "error"
                ], 401)
        ];
    }

    if (is_array($allowedLevel) && in_array($userLevel, $allowedLevel) === false) {
        return [
            "allowed" => false,
            "return" => $response->withJson([
                "message" => "Invalid User",
                "status" => "error"
                ], 401)
        ];
    }

    return [
        "allowed" => true,
        "return" => $dataUser
    ];
}
