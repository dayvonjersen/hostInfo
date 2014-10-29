<?php
/**
 * function: bool is_numeric_array ( array $array )
 * 
 * I wrote this but so have half a million other people.
 * 
 */
function is_numeric_array($array)
{
    if(is_array($array))
    {
        foreach($array as $key => $value)
        {
            if(!is_int($key))
                return false;
        }
        return true;
    }
    return false;
}

/**
 * http_referer_is_host()
 * 
 * @comment does what the name implies
 * @usage csrf checks and redirects
 * @return boolean */
function http_referer_is_host()
{
    return (isset($_SERVER['HTTP_REFERER']) && 
        stristr($_SERVER['HTTP_REFERER'],$_SERVER['HTTP_HOST']));
}

/**
 * require_login()
 *
 * application-specific implementation 
 * TODO: role-based authorization stuff for admin pages
 */
function require_login()
{
    if(!Account::getCurrent()->isLoggedIn())
    {
        $flash = new FlashMessage('site-wide');
        $flash->add('require-login',1,FlashMessage::NOTICE);
        $_SESSION['FlashMessage'] = serialize($flash);
        header('Location: '.HTTP_DIR.'login');
        exit;
    }
}
