<?php

namespace app;

use DateTime;

require_once __DIR__ . "/Model.php";

class Controller
{

    private Model $model;
    private Validator $validator;

    /**
     * Constructor for the class.
     *
     * Initializes the model and validator objects.
     */
    public function __construct()
    {
        $this->model = new Model();
        $this->validator = new Validator();
    }

    /**
     * Adds the login to the database if it is unique and meets the validation criteria.
     *
     * @param array $postData The array containing the post data.
     * @return array The array containing the message ID, message type, create date, write to DB flag,
     * error message, login, and status in the queue.
     */
    public function addLoginToDBIfUnique(array $postData): array
    {
        // Get the login from the post data
        $login = $postData["login"] ?? "";
        // Check if the login is unique
        $loginUnique = $this->checkUnique($login);
        // Validate the login
        $validationResult = $this->validator->validateLogin($login, $loginUnique);
        // Update the online status for the login
        $this->updateOnlineStatus($login);
        // Check the user status on the queue
        $status = $this->checkUserStatusOnQueue($login);
        // If the validation is successful and the login is unique, add it to the database
        if ($validationResult === "" && $loginUnique) {
            $this->addLoginToDB($login);
        }

        // Create and return the response array
        return [
            'messageId' => 5,
            'messageType' => 'loginRegisteredInDB',
            'createDate' => new DateTime(),
            'isWriteToDB' => !($validationResult),
            'errMsg' => $validationResult,
            'login' => $login,
            'status' => $status
        ];
    }

    /**
     * Checks if the given login is unique.
     *
     * @param string $login The login to check.
     * @return bool Returns true if the login is unique, false otherwise.
     */
    private function checkUnique(string $login): bool
    {
        return $this->model->isLoginUnique($login);
    }

    /**
     * Retrieves the status of a user on the queue.
     *
     * @param string $login The login of the user.
     * @return int The status of the user on the queue.
     */
    private function checkUserStatusOnQueue(string $login): int
    {
        return $this->model->getUserStatusFromQueues($login);
    }

    /**
     * Update the online status for a given login.
     *
     * @param string $login The login of the user.
     */
    private function updateOnlineStatus(string $login)
    {
        $this->model->updateOnlineStatus($login);
    }

    /**
     * Adds a login to the database.
     *
     * @param string $login The login to be added.
     */
    private function addLoginToDB(string $login)
    {
        $this->model->addLoginToDB($login);
    }

//    public function updateUserStatusInQueues($login, $status): bool
//    {
//        return $this->model->updateUserStatusInQueues($login, $status);
//    }
//
//    public function findUsersThatSearchForGame($login): int
//    {
//        return $this->model->getUserIdWhereStatusInSearch($login);
//    }
//
//    public function createNewGame($login, $randNumber): int
//    {
//        return $this->model->createNewGameInGames($login, $randNumber);
//    }
//
//    public function getSecondUserLogin(int $userIdInSearch): string
//    {
//        return $this->model->getSecondUserLoginFromUsers($userIdInSearch);
//    }
//
//    public function connectToGame($login, $first_player, $randNumber): array
//    {
//        return $this->model->connectToCurrentGame($login, $first_player, $randNumber);
//    }
//
//    public function getFirstTurnFromDB(int $newGameId): int
//    {
//        return $this->model->getSecondPlayerRollFromGames($newGameId);
//    }
//
//    public function deleteEmptyGame($login): bool
//    {
//        return $this->model->deleteGameWithEmptySecondPlayerFromGames($login);
//    }
//
//    public function addShipsAndCoordinates($shipCoordinates, int $gameId, $login)
//    {
//        $this->model->addShipAndCoordinatesToPrivateTable($shipCoordinates, $gameId , $login);
//    }
//
//    public function removePlayerFromQueue($login)
//    {
//        $this->model->deleteUserFromQueues($login);
//    }
//
//    public function removePlayerFromUserList($login)
//    {
//        $this->model->deleteUserFromUsers($login);
//    }
//
//    public function formShipsJSON($login): array
//    {
//        return $this->model->getShipsFromDB($login);
//    }
//
//    public function getCurrentGameInfo($login): array
//    {
//        return $this->model->getGameRecordFromGames($login);
//    }
//
//    public function sendShotToOpponent($gameId, $shotCoords, $login)
//    {
//        $this->model->sendRequestToShots($gameId, $shotCoords, $login);
//    }
//
//    public function getApprovalStatusFromOpponent($gameId, $shotCoords, $login)
//    {
//        for ($i = 0; $i < 25; $i++) {
//            $status = $this->model->getResponseStatusFromShots($gameId, $shotCoords, $login);
//            if ($status[0] !== null) {
//                return $status;
//            }
//            usleep(200000);
//        }
//    }
//
//    /**
//     * @throws Exception
//     */
//    public function userOnline(int $opponentId): int
//    {
//        return $this->model->getUserOnlineStatusFromUsers($opponentId);
//    }
//
//    public function listenRequestFromOpponent($gameId, $login): ?array
//    {
//        for ($i = 0; $i < 150; $i++) {
//            $target = $this->model->getRequestFromShots($gameId, $login);
//            if($target !== "") {
//                return [$target, $this->model->checkIfOpponentHit($target, $gameId, $login)];
//            }
//            usleep(200000);
//        }
//        return null;
//    }
//
//    public function checkSecondUserConnect(int $newGame): int
//    {
//        return $this->model->checkUserIdInGames($newGame);
//    }
//
//    public function getWinnerOfGame($gameId, $login, $opponent): int
//    {
//        return $this->model->getWinnerFromGamesIfGameIsEnd($gameId, $login, $opponent);
//    }
//
//    public function getWinnerForRequester($gameId, $login, $opponent): int
//    {
//        return $this->model->getWinnerFromGamesIfYouEndGame($gameId, $login, $opponent);
//    }
//
//    public function updateWinner($gameId, $login)
//    {
//        $this->model->updateWinnerInGames($gameId, $login);
//    }
//
//    public function getDestroyedShip($gameId, $target, $login): array
//    {
//        return $this->model->getShipWithTargetAndWithDestroyed($gameId, $target, $login);
//    }
//
//    public function updateLastTime($login)
//    {
//        $this->model->updateTimeInUsers($login);
//    }
//
//    public function getWinner($gameId, $login, $opponent)
//    {
//        return $this->model->getWinnerFromGamesIfGameIsEnd($gameId, $login, $opponent);
//    }
}