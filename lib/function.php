<?php

function validate($user)
{
    $errors = [];
    if (empty($user['nickname'])) {
        $errors['nickname'] = "Can't be blank";
    }
    return $errors;
}

function decodeUsers($array)
{
    $result = [];
    foreach ($array as $vol) {
        $result[] = json_decode($vol, true);
    }
    return $result;
}


function checkId($decodeUsers)
{
    $checkId = rand();
    foreach ($decodeUsers as ['nickname' => $nickname, 'email' => $email, 'id' => $id]) {
        if ($checkId === $id) {
            return isId($decodeUsers); // похоже должно вернуть false а функция isId потерялась...
        }
    }
    return $checkId;
}


function setUserId($str)
{
    $explode = explode("|", $str);
    $decodeUsers = decodeUsers($explode);
    return checkId($decodeUsers);
}

//========================= parse from file ===================
function parseUsers($file)
{
    $textUsers = file_get_contents(__DIR__ . $file);
    $strJson = explode("|", $textUsers);

    $users = array_map(function ($user) {
        return json_decode($user, true);
    }, $strJson);

    $not_empty = array_filter($users, function ($user) {
        return !empty($user);
    });
    return $not_empty;
}

// ==============================================================
function isUser($findUser, $users)
{
    $res = array_filter($users, function ($user) use ($findUser) {
        if ($user['nickname'] === $findUser) {
            return $user['nickname'];
        }
    });
    return $res ? true : false;
}

// ===============================================================

function isUserId($findUserId, $users)
{
    $res = array_filter($users, function ($user) use ($findUserId) {
        if ((string) $user['id'] === $findUserId) {
            return $user['id'];
        }
    });
    return $res ? true : false;
}

// =================================================================

function isUserById($findUserId, $users)
{
    $result = array_filter($users, function ($user) use ($findUserId) {
        if ($user['id'] == $findUserId) {
            return $user;
        }
    });
    [$elem] = array_values($result);
    return $elem ? $elem : false;
}


//===================================================================

function editUser($userId, $editData, $users)
{
    $res = array_map(function ($user) use ($userId, $editData) {

        if ($user['id'] == $userId) {
            $user['nickname'] = $editData;
        }
        return $user;
    }, $users);
    return $res;
    // запись массива в файл нужно
}

//======================================================================
function arrToJson($arr)
{
    $str = "";
    foreach ($arr as $vol) {
        $str .= json_encode($vol) . "|\n";
    }
    return $str;
}

// ====================================================================

function deleteUser($delUser, $users)
{
    $result = array_filter($users, function ($user) use ($delUser) {
        if ($user['id'] != $delUser) {
            return $user;
        }
    });
    return $result;
}