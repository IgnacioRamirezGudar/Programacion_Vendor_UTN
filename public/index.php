<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use \Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . "/../src/poo/clases/Usuarios.php";
require_once __DIR__ . "/../src/poo/clases/Perfil.php";
//require_once __DIR__ . "/../poo/MW.php";

$app = AppFactory::create();

$app->post('/usuario', \RamirezGudar\Ignacio\Usuario::class . ':alta_usuario');

$app->get('/', \RamirezGudar\Ignacio\Usuario::class . ':listar_usuarios');

$app->post('/', \RamirezGudar\Ignacio\Perfiles::class . ':alta_perfil');

$app->get('/perfil', \RamirezGudar\Ignacio\Perfiles::class . ':listar_perfiles');

$app->post("/login", \RamirezGudar\Ignacio\Usuario::class . ':loginUser');

$app->get("/login", \RamirezGudar\Ignacio\Usuario::class . ':verificar_userJWT');

$app->group('/perfiles', function (RouteCollectorProxy $gperfil) {
    $gperfil->delete("/{id}", \RamirezGudar\Ignacio\Perfiles::class . ':borrar_perfil');
    $gperfil->post("/", \RamirezGudar\Ignacio\Perfiles::class . ':modificar_perfil');
});

$app->run();

?>