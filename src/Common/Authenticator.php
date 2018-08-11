<?php
namespace HTR\Common;

use App\System\Configuration as cfg;
use Firebase\JWT\JWT;

/**
 * This class configure the JWT used in system
 *
 * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
 * @since 1.0
 */
class Authenticator
{

    /**
     * Generate the JSON Web Token
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @param array $options
     * @return string
     */
    public static function generateToken(array $options): string
    {
        $issuedAt = time();
        $expire = $issuedAt + $options['expiration_sec']; // tempo de expiracao do token

        $tokenParam = [
            'iat' => $issuedAt, // timestamp de geracao do token
            'iss' => $options['host'], // dominio, pode ser usado para descartar tokens de outros dominios
            'exp' => $expire, // expiracao do token
            'nbf' => $issuedAt - 1, // token nao eh valido Antes de
            'data' => $options['userdata'], // Dados do usuario logado
        ];

        return JWT::encode($tokenParam, cfg::SALT_KEY);
    }

    /**
     * Decode the JWT used by user
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @param string $token
     * @return \stdClass
     */
    public static function decodeToken(string $token): \stdClass
    {
        return JWT::decode($token, cfg::SALT_KEY, ['HS256']);
    }
}
