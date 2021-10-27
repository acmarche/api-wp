<?php

namespace AcMarche\ApiWp;

use Symfony\Component\Dotenv\Dotenv;

class Env
{
    public static function loadEnv(): void
    {
        $dotenv = new Dotenv();
        $dir    = getcwd();
        try {
            $dotenv->bootEnv(ABSPATH.'/.env');
        } catch (\Exception $exception) {
            echo "Error load env: ".$exception->getMessage();
        }
    }
}
