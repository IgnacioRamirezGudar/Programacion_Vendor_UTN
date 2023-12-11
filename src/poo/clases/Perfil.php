<?php
namespace RamirezGudar\Ignacio{


    use Firebase\JWT\JWT;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
    use Slim\Psr7\Response as ResponseMW;
    use stdClass;

    use connectDB;
    use PDO;
    use PDOException;
    use Autentificadora;

    class Perfiles{

        public int $id;
        public string $descripcion;
        public int $estado;


        public function alta_perfil(Request $request, Response $response, array $args): Response{

            $array = $request->getParsedBody();

            $objperfil = $array['perfil_json'];
            $obj = json_decode($objperfil);
    
            $newperfil = new Perfiles();
            $newperfil->descripcion = $obj->descripcion;
            $newperfil->estado = $obj->estado;

            $result = $newperfil->agregar_perfilBD();
    
            $std = new stdclass();
    
            if ($result == true) {
    
                $newResponse = $response->withStatus(200, "Exito!!! Se añadio.");
                $std->exito = true;
                $std->mensaje = "Perfil añadido!";
                $std->user = $obj;
                $newResponse->getBody()->write(json_encode($std, 200));
            } else {
    
                $newResponse = $response->withStatus(418, "ERROR!!!!");
                $std->exito = false;
                $std->mensaje = "Perfil no añadido!";
                $std->user = $obj;
                $newResponse->getBody()->write(json_encode($std, 418));
    
            }
    
            return $newResponse->withHeader('Content-Type', 'application/json');

        }


        public function listar_perfiles(Request $request, Response $response, array $args): Response{


            $obj_respuesta = new stdClass();
            $obj_respuesta->exito = false;
            $obj_respuesta->mensaje = "No se encontraron perfiles!";
            $obj_respuesta->dato = "{}";
            $obj_respuesta->status = 424;

            $perfiles = Perfiles::traer_perfiles();

            if(count($perfiles)){
                $obj_respuesta->exito = true;
                $obj_respuesta->mensaje = "Perfiles encontrados!";
                $obj_respuesta->dato = json_encode($perfiles);
                $obj_respuesta->status = 200;    
            }
    
            $newResponse = $response->withStatus($obj_respuesta->status);
            $newResponse->getBody()->write(json_encode($obj_respuesta));
            return $newResponse->withHeader('Content-Type', 'application/json');

        }


        public function borrar_perfil(Request $request, Response $response, array $args): Response
        {
            $obj_respuesta = new stdclass();
            $obj_respuesta->exito = false;
            $obj_respuesta->mensaje = "No se pudo borrar el perfil";
            $obj_respuesta->status = 418;
    
            if (
                isset($request->getHeader("token")[0]) &&
                isset($args["id"])
            ) {
                $token = $request->getHeader("token")[0];
                $id_perfil = $args["id"];
    
                $datos_token = Autentificadora::obtenerPayLoad($token);
    
                if (Perfiles::borrar_perfilBD($id_perfil)) {
                    $obj_respuesta->exito = true;
                    $obj_respuesta->mensaje = "Perfil Borrado!";
                    $obj_respuesta->status = 200;
                } else {
                    $obj_respuesta->mensaje = "El Perfil no existe en el listado!";
                }
            }
    
            $newResponse = $response->withStatus($obj_respuesta->status);
            $newResponse->getBody()->write(json_encode($obj_respuesta));
            return $newResponse->withHeader('Content-Type', 'application/json');
        }

        public function modificar_perfil(Request $request, Response $response, array $args): Response
        {
            $parametros = $request->getParsedBody();

            $obj_respuesta = new stdclass();
            $obj_respuesta->exito = false;
            $obj_respuesta->mensaje = "No se pudo modificar el perfil";
            $obj_respuesta->status = 418;

            if (
                isset($request->getHeader("token")[0])) {
                
                $token = $request->getHeader("token")[0];
                $objperfil = $parametros['perfil_json'];
                $obj_json = json_decode($objperfil);

                $newperfil = new Perfiles();
                $newperfil->id = $obj_json->id;
                $newperfil->descripcion = $obj_json->descripcion;
                $newperfil->estado = $obj_json->estado;
                
                if ($newperfil->modificar_perfilBD()) {
                    $obj_respuesta->exito = true;
                    $obj_respuesta->mensaje = "Perfil Modificado!";
                    $obj_respuesta->status = 200;
                }
            }

            $newResponse = $response->withStatus($obj_respuesta->status);
            $newResponse->getBody()->write(json_encode($obj_respuesta));
            return $newResponse->withHeader('Content-Type', 'application/json');
        }







        public function agregar_perfilBD(): bool
        {
            $retorno = true;
    
            try {
    
                $objDB = connectDB::objAccess();
    
                $sql = $objDB->rtnSql("INSERT INTO `perfiles`(`descripcion`, `estado`)"
                    . "VALUES (:descripcion,:estado)");
    
    
                $sql->bindValue(':descripcion', $this->descripcion, PDO::PARAM_STR);
                $sql->bindValue(':estado', $this->estado, PDO::PARAM_INT);
                
    
                $sql->execute();
            } catch (PDOException) {
    
                $retorno = false;
            }
    
            return $retorno;
        }


        public static function traer_perfiles()
        {
    
            $query = connectDB::objAccess();
    
            $sql = $query->rtnSql("SELECT * FROM perfiles");
    
            $sql->execute();
    
            return $sql->fetchAll(PDO::FETCH_ASSOC);
        }

        public static function borrar_perfilBD(int $id_perfil)
        {
            $retorno = false;
            $query = connectDB::objAccess();
            $sql = $query->rtnSql("DELETE FROM perfiles WHERE id = :id_perfil");
            $sql->bindValue(":id_perfil", $id_perfil, PDO::PARAM_INT);
            $sql->execute();
    
            $total_borrado = $sql->rowCount();
            if ($total_borrado == 1) {
                $retorno = true;
            }
    
            return $retorno;
        }

        public function modificar_perfilBD()
        {
            $retorno = false;
    
            $query = connectDB::objAccess();
    
            $sql = $query->rtnSql(
                "UPDATE perfiles SET descripcion = :descripcion, estado = :estado WHERE id = :id"
            );
    
            $sql->bindValue(":id", $this->id, PDO::PARAM_INT);
            $sql->bindValue(":descripcion", $this->descripcion, PDO::PARAM_STR);
            $sql->bindValue(":estado", $this->estado, PDO::PARAM_INT);
            $sql->execute();
    
            $total_modificado = $sql->rowCount();
            if ($total_modificado == 1) {
                $retorno = true;
            }
    
            return $retorno;
        }


    }

}


?>