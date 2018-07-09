<?php
namespace Controller;

/**
 * Class Main
 * @package Controller
 */
class Main
{
    public function index()
    {
        echo 'Route Index';
    }

    public function notfound()
    {
        echo 'notfound';
    }

    public function restful()
    {
        echo 'restful';
        return 'Reeasf';
    }
}