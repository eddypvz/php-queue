<?php

if (!function_exists("dd")) {
    function dd($var) {
        print_r("<pre>");
        print_r($var);
        print_r("</pre>");
    }
}