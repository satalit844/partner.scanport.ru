<?php
if (count($_POST))
{
    $flag = false;
    $now = microtime(true);

    if ((isset($_SESSION['now'])) && (($now - $_SESSION['now']) > 4))
    {
        $flag = true;
    }
    return $flag;
}
else
{
    $_SESSION['now'] = microtime(true);
}