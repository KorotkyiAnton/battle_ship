<?php

namespace app;
require_once __DIR__ . "/Model.php";

class Controller
{

    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    public function validateLogin($login): string
    {
        $errorMsg = "";

        // Проверка длины логина
        if (strlen($login) < 3 || strlen($login) > 10) {
            $errorMsg .= "Нажаль, помилка - дозволена довжина нікнейму від 3 до 10 символів;<br>";
        }

        // Проверка символов логина
        if (!preg_match("/^[0-9А-яA-Za-zЁёЇїІіЄєҐґ\-\'_]+$/", $login)) {
            $errorMsg .= "На жаль, помилка - нікнейм може містити літери (zZ-яЯ),цифри (0-9), спецсимволи (Word space, -, ', _);<br>";
        }

        // Проверка начального символа
        if (!preg_match("/^[0-9А-яA-Za-zЁёЇїІіЄєҐґ]/", $login[0])) {
            $errorMsg .= "Нікнейм повинен починатися з літер чи цифр;";
        }

        // Проверка конечного символа
        if (!preg_match("/^[0-9А-яA-Za-zЁёЇїІіЄєҐґ]$/", $login[strlen($login) - 1])) {
            $errorMsg .= "Нікнейм повинен закінчуватися на літеру чи цифру;<br>";
        }

        if (!$this->checkUnique($login)) {
            $errorMsg .= "На жаль, помилка - данний нікнейм вже зайнятий, спробуйте інший.<br>";
        }

        if ($errorMsg === "") {
            $this->model->addLoginToDB($login);
        }

        return $errorMsg;
    }

    private function checkUnique($login): bool
    {
        return $this->model->isLoginUnique($login);
    }

    public function checkUserStatusOnQueue($login): int
    {
        return $this->model->getUserStatusFromQueues($login);
    }

    public function updateUserStatusInQueues($login, $status): bool
    {
        return $this->model->updateUserStatusInQueues($login, $status);
    }

    public function findUsersThatSearchForGame($login): int
    {
        return $this->model->getUserIdWhereStatusInSearch($login);
    }

    public function createNewGame($login, $randNumber): int
    {
        return $this->model->createNewGameInGames($login, $randNumber);
    }

    public function getSecondUserLogin(int $userIdInSearch): string
    {
        return $this->model->getSecondUserLoginFromUsers($userIdInSearch);
    }

    public function connectToGame($login, $first_player, $randNumber): array
    {
        return $this->model->connectToCurrentGame($login, $first_player, $randNumber);
    }

    public function getFirstTurnFromDB(int $newGameId): int
    {
        return $this->model->getSecondPlayerRollFromGames($newGameId);
    }

    public function deleteEmptyGame($login): bool
    {
        return $this->model->deleteGameWithEmptySecondPlayerFromGames($login);
    }

    public function addShipsAndCoordinates($shipCoordinates, int $gameId, $login)
    {
        $this->model->addShipAndCoordinatesToPrivateTable($shipCoordinates, $gameId , $login);
    }

    public function removePlayerFromQueue($login)
    {
        $this->model->deleteUserFromQueues($login);
    }

    public function removePlayerFromUserList($login)
    {
        $this->model->deleteUserFromUsers($login);
    }

    public function formShipsJSON($login): array
    {
        return $this->model->getShipsFromDB($login);
    }

    public function getCurrentGameInfo($login): array
    {
        return $this->model->getGameRecordFromGames($login);
    }

    public function sendShotToOpponent($gameId, $shotCoords, $login)
    {
        $this->model->sendRequestToShots($gameId, $shotCoords, $login);
    }

    public function getApprovalStatusFromOpponent($gameId, $shotCoords, $login)
    {
        for ($i = 0; $i < 5; $i++) {
            $status = $this->model->getResponseStatusFromShots($gameId, $shotCoords, $login);
            if ($status !== null) {
                return $status;
            }
            sleep(1);
        }
    }

    public function userOnline(int $opponentId): bool
    {
        return $this->model->getUserOnlineStatusFromUsers($opponentId);
    }

    public function listenRequestFromOpponent($gameId, $login): ?array
    {
        for ($i = 0; $i < 30; $i++) {
            $target = $this->model->getRequestFromShots($gameId, $login);
            if($target !== "") {
                return [$target, $this->model->checkIfOpponentHit($target, $gameId, $login)];
            }
            sleep(1);
        }
        return null;
    }

    public function checkSecondUserConnect(int $newGame): int
    {
        return $this->model->checkUserIdInGames($newGame);
    }

    public function getWinnerOfGame($gameId, $login, $opponent): int
    {
        return $this->model->getWinnerFromGamesIfGameIsEnd($gameId, $login, $opponent);
    }

    public function getWinnerForRequester($gameId, $login, $opponent): int
    {
        return $this->model->getWinnerFromGamesIfYouEndGame($gameId, $login, $opponent);
    }

    public function updateWinner($gameId, $login)
    {
        $this->model->updateWinnerInGames($gameId, $login);
    }
}