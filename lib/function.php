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
  foreach ($decodeUsers as ['nickname' => $nickname, 'email' => $email, 'id' => $id]){
    if ($checkId === $id) {
      return isId($decodeUsers);
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