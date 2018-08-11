<?php
namespace HTR\Common;

use HTR\Common\Authenticator;
use Slim\Http\Request;
use App\System\Configuration as cfg;
use HTR\Database\EntityAbstract as db;

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
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @param Request $request
     * @return boolean|\stdClass
     */
    public static function token(Request $request)
    {
        $authorization = $request->getHeader('Authorization');
        if (empty($authorization)) {
            return false;
        }

        $token = preg_replace('/^JWT\s/', '', $authorization[0]);

        return Authenticator::decodeToken($token);
    }

    /**
     * Returns the data of User according Database
     *
     * @author Edson B S Monteiro <bruno.monteirodg@gmail.com>
     * @since 1.0
     * @param Request $request
     * @return boolean|{UserEntity}
     */
    public static function user(Request $request)
    {
        $data = self::token($request);

        if (!$data) {
            return false;
        }

        $repository = db::em()->getRepository(cfg::USER_ENTITY);

        return $repository->find($data->data->id ?? 0);
    }
}
