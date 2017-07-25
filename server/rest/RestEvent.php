<?php
/**
 * Created by PhpStorm.
 * event: Paul
 * Date: 14/07/2017
 * Time: 19:51.
 */

namespace server\rest;

require_once __DIR__.'/../vendor/autoload.php';

require_once __DIR__.'/../database/DBConnection.php';
require_once __DIR__.'/../database/DBEvent.php';
require_once __DIR__.'/RestServer.php';

use server\database\DBConnection;
use server\database\DBEvent;
use Slim\Http\Request;
use Slim\Http\Response;

class RestEventCreation
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
        $verification = RestServer::getRequiredParams($response, ['name', 'date', 'description', 'address', 'theme']);

        // Parameters corresponds
        if ($verification['status']) {
            $authentication = RestServer::authenticate();

            // Authentication success
            if (is_int($authentication)) {
                return $this->eventCreation($verification['response'], $response, $authentication);
            }

            return RestServer::createJSONResponse($response, 400, $authentication);
        }

        return RestServer::createJSONResponse($response, 400, $verification['response']);
    }

    /**
     * @param $data
     * @param Response $response
     * @param $user_id
     *
     * @return Response
     */
    public function eventCreation($data, Response $response, $user_id)
    {
        // reading post params
        $name = RestServer::getSecureParam($data['name']);
        $date = RestServer::getSecureParam($data['date']);
        $description = RestServer::getSecureParam($data['description']);
        $address = RestServer::getSecureParam($data['address']);
        $theme = RestServer::getSecureParam($data['theme']);

        $connection = new DBConnection();

        // event validation
        $event = new DBEvent(null);

        $res = $event->eventCreate($connection, [
            'event_user_id'             => $user_id,
            'event_name'                => $name,
            'event_description'         => $description,
            'event_date'                => $date,
            'event_address'             => $address,
            'event_theme'               => $theme,
            'event_secret'              => 0,
        ]);

        if ($res > -1) {
            $response = RestServer::createJSONResponse($response, 200, 'EVENT_CREATED_SUCCESSFULLY');
        } else {
            $response = RestServer::createJSONResponse($response, 400, 'EVENT_CREATE_FAILED');
        }

        return $response;
    }
}

class RestEventEdit
{
}

class RestEventUserListOwn
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
        $authentication = RestServer::authenticate();

        // Authentication success
        if (is_int($authentication)) {
            return $this->eventUserList($response, $authentication);
        }

        return RestServer::createJSONResponse($response, 400, $authentication);
    }

    /**
     * @param Response $response
     * @param $user_id
     *
     * @return Response
     */
    public function eventUserList(Response $response, $user_id)
    {
        $connection = new DBConnection();

        // event validation
        $event = new DBEvent(null);

        return RestServer::createJSONResponse($response, 200, json_encode($event->eventUserList($connection, $user_id)));
    }
}