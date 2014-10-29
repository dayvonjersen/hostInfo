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

// LOL THAT WAS FROM SOMETHING ELSE MOVE ALONG
