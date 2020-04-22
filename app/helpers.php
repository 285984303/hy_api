<?php

if (!function_exists('options_filter')) {
    function options_filter(array $array) {
        return array_filter($array,function($value) {
           return is_array($value) ? count($value) : strlen($value);
        });
    }
}