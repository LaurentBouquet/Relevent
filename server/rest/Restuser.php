<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 14/07/2017
 * Time: 19:51.
 */

namespace server\rest;

require_once __DIR__.'/../vendor/autoload.php';

require_once __DIR__.'/../database/DBconnection.php';
require_once __DIR__.'/../database/DBuser.php';
require_once __DIR__.'/RestServer.php';

use server\database\DBconnection;
use server\database\DBuser;
use Slim\Http\Request;
use Slim\Http\Response;

class RestUserCreation
{
    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $args = [])
    {
        $verification = RestServer::getRequiredParams($response, ['nickname', 'mail', 'password']);

        if ($verification['status']) {
            return $this->userRegister($verification['response'], $response);
        }

        return $response
            ->withStatus(400)
            ->withHeader('Content-type', 'application/json')
            ->withJson(RestServer::createJSONResponse($verification['response']));
    }

    /**
     * @param $data
     * @param Response $response
     *
     * @return Response
     */
    public function userRegister($data, Response $response)
    {
        // reading post params
        $name = RestServer::getSecureParam($data['nickname']);
        $email = RestServer::getSecureParam($data['mail']);
        $password = RestServer::getSecureParam($data['password']);

        $connection = new DBConnection();

        // User validation
        $user = new DBuser(null);

        // validating email address
        if ($user->userMailExists($connection, $email)) {
            $status  = 400;
            $message = 'USER_MAIL_EXISTS';

            // validating nickname
        } elseif ($user->userNickNameExists($connection, $name)) {
            $status  = 400;
            $message = 'USER_NICKNAME_EXISTS';

            // User validated
        } else {
            $res = $user->userCreate($connection, [
                'user_nickname'     => $name,
                'user_mail'         => $email,
                'user_password'     => $password,
            ]);

            if ($res > -1) {
                $status  = 200;
                $message = 'USER_CREATED_SUCCESSFULLY';
            } else {
                $status  = 400;
                $message = 'USER_CREATE_FAILED';
            }
        }

        return $response
            ->withStatus($status)
            ->withHeader('Content-type', 'application/json')
            ->withJson(RestServer::createJSONResponse($message));
    }
}

class RestUserLogin
{
    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return RestUserLogin|Response
     */
    public function __invoke(Request $request, Response $response, $args = [])
    {
        $verification = RestServer::getRequiredParams($response, ['nickname', 'password']);

        if ($verification['status']) {
            return $this->userLogin($verification['response'], $response);
        }

        return $response
            ->withStatus(400)
            ->withHeader('Content-type', 'application/json')
            ->withJson(RestServer::createJSONResponse($verification['response']));
    }

    /**
     * @param $data
     * @param Response $response
     *
     * @return Response
     */
    public function userLogin($data, Response $response)
    {
        // reading post params
        $name = RestServer::getSecureParam($data['nickname']);
        $password = RestServer::getSecureParam($data['password']);

        $connection = new DBConnection();

        // User validation
        $user = new DBuser(null);

        // validating email address
        if ($user->tryLogin($connection, $name, $password)) {
            $message = 'USER_LOGIN_SUCCESSFULLY';
            $status = 200;

            // Connection failed
        } else {
            $message = 'USER_LOGIN_FAILED';
            $status = 400;
        }

        return $response
            ->withStatus($status)
            ->withHeader('Content-type', 'application/json')
            ->withJson(RestServer::createJSONResponse($message));
    }
}
