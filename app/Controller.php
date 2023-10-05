<?php

namespace app;

use DateTime;

require_once __DIR__ . "/Model.php";

class Controller
{

    private Model $model;
    private Validator $validator;

    /**
     * Конструктор класса.
     *
     * Инициализирует объекты модели и валидатора.
     */
    public function __construct()
    {
        $this->model = new Model();
        $this->validator = new Validator();
    }

    /**
     * Добавляет логин в базу данных, если он уникален.
     *
     * @param array $postData JSON-данные с фронт-энда, содержащие логин.
     * @return array Результат добавления логина в базу данных.
     */
    public function addLoginToDBIfUnique(array $postData): array
    {
        $login = $postData["login"] ?? "";
        $loginUnique = $this->model->isLoginUnique($login);
        $validationResult = $this->validator->validateLogin($login, $loginUnique);
        $this->model->updateOnlineStatus($login);
        $status = $this->model->getUserStatusFromQueues($login);

        return $this->addLoginToDB($login, $loginUnique, $validationResult, $status);
    }

    /**
     * Добавляет логин в базу данных и возвращает массив с соответствующей информацией.
     *
     * @param string $login Логин, который нужно добавить в базу данных.
     * @param bool $loginUnique Определяет, уникален ли логин или нет.
     * @param string $validationResult Результат процесса валидации.
     * @param int $status Состояние логина.
     * @return array Массив со следующими ключами:
     *               - 'messageId': Идентификатор сообщения.
     *               - 'messageType': Тип сообщения.
     *               - 'createDate': Дата создания сообщения.
     *               - 'isWriteToDB': Указывает, был ли логин записан в базу данных.
     *               - 'errMsg': Сообщение об ошибке, если оно есть.
     *               - 'login': Значение логина.
     *               - 'status': Состояние логина.
     */
    public function addLoginToDB(string $login, bool $loginUnique, string $validationResult, int $status): array
    {
        if ($validationResult === "" && $loginUnique) {
            $this->model->addLoginToDB($login);
        }

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