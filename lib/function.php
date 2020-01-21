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

//=========================parse from file ===================
function parseUsers($file)
{
    $textUsers = file_get_contents(__DIR__ . $file);
    $strJson = explode("|", $textUsers);
    $users = array_map(function ($user) {
        return json_decode($user, true);
    }, $strJson);
    return array_diff($users, ['']);
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
