<?php
namespace HTR\Common;

use HTR\Common\Authenticator;
use Slim\Http\Request;
use App\System\Configuration as cfg;
use HTR\Database\EntityAbstract as db;
use HTR\Exceptions\HeaderWithoutAuthorizationException;

/**
 * This class is used like a helper getting auth properties
 *
 * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
 * @since 1.0
 */
class ValidateAuthentication
{

    /**
     * Returns the data of token passed on Request
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @param Request $request
     * @return \stdClass
     * @throws HeaderWithoutAuthorizationException
     */
    public static function token(Request $request)
    {
        $authorization = $request->getHeader('Authorization');

        if (isset($authorization[0])) {
            throw new HeaderWithoutAuthorizationException('The request does not contain the Authorization header');
        }

        $token = preg_replace('/^\w+\s/', '', $authorization[0]);

        return Authenticator::decodeToken($token);
    }

    /**
     * Returns the data of User according Database
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @param Request $request
     * @return boolean|{UserEntity}
     */
    public static function user(Request $request)
    {
        try {

            $data = self::token($request);
            $repository = db::em()->getRepository(cfg::USER_ENTITY);
            return $repository->find($data->data->id ?? 0);
        } catch (HeaderWithoutAuthorizationException $ex) {
            return false;
        }
    }
}
