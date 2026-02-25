<?php

function cleanInput($input)
{
    return filter_var($input, FILTER_SANITIZE_SPECIAL_CHARS);
}

function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isValidroom($room) {
    return strlen(trim($room)) > 0; // accept any non-empty string
}


function dnd($variable)
{
    echo '<pre>';
    var_dump($variable);
    echo '</pre>';
    die;
}