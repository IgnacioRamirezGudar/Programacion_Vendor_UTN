<?php
namespace RamirezGudar\Ignacio{

    use Firebase\JWT\JWT;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
    use Slim\Psr7\Response as ResponseMW;
    use stdClass;

    require_once "coonectBD.php";
    require_once "JWT.php";

    use connectDB;
    use PDO;
    use PDOException;
    use Autentificadora;
    
    class Usuario{

        public string $correo;
        public int $clave;
        public string $nombre;
        public string $apellido;
        public int $id_perfil;
        public string $foto;

        public function alta_usuario(Request $request, Response $response, array $args): Response{

            $array = $request->getParsedBody();

            $archivos = $request->getUploadedFiles();
    
            $extension = explode(".", $archivos['foto']->getClientFilename());
    
            $objusuario = $array['usuario_json'];
            $obj = json_decode($objusuario);
    
            $destino = "../src/fotos/";
    
            $img = $obj->apellido . "." . $extension[1];
    
            $final_path = $destino . $img;
    
            $usuario = new Usuario();
            $usuario->correo = $obj->correo;
            $usuario->clave = $obj->clave;
            $usuario->nombre = $obj->nombre;
            $usuario->apellido = $obj->apellido;
            $usuario->id_perfil = $obj->id_perfil;
            $usuario->foto = $final_path;

            $result = $usuario->agregar_usuariosBD();
    
            $std = new stdclass();
    
            if ($result == true) {
    
                $archivos['foto']->moveTo($destino . $img);
                $newResponse = $response->withStatus(200, "Exito!!! Se añadio.");
                $std->exito = true;
                $std->mensaje = "Usuario añadido!";
                $std->user = $obj;
                $newResponse->getBody()->write(json_encode($std, 200));
            } else {
    
                $newResponse = $response->withStatus(418, "ERROR!!!!");
                $std->exito = false;
                $std->mensaje = "Usuario no añadido!";
                $std->user = $obj;
                $newResponse->getBody()->write(json_encode($std, 418));
    
            }
    
            return $newResponse->withHeader('Content-Type', 'application/json');

        }

      
        public function listar_usuarios(Request $request, Response $response, array $args): Response{

            $obj_respuesta = new stdClass();
            $obj_respuesta->exito = false;
            $obj_respuesta->mensaje = "No se encontraron los usuarios!";
            $obj_respuesta->dato = "{}";
            $obj_respuesta->status = 424;


            $usuario = Usuario::traer_usuarios();

            if(count($usuario)){
                $obj_respuesta->exito = true;
                $obj_respuesta->mensaje = "Usuarios encontrados!";
                $obj_respuesta->dato = json_encode($usuario);
                $obj_respuesta->status = 200;    
            }
    
            $newResponse = $response->withStatus($obj_respuesta->status);
            $newResponse->getBody()->write(json_encode($obj_respuesta));
            return $newResponse->withHeader('Content-Type', 'application/json');

        }


        //PARTE LOGIN
        public function loginUser(Request $request, Response $response, array $args) : Response {

            $arrayDeParametros = $request->getParsedBody();

            $objusuario = $arrayDeParametros['user'];
    
            $obj = json_decode($objusuario);

            $token = Autentificadora::crearJWT($obj, 45);
    
            $response->withStatus(200);
    
            $response->getBody()->write(json_encode($token));
        
            return $response->withHeader('Content-Type', 'application/json');

        }


        public function verificar_userJWT(Request $request, Response $response, array $args) : Response {

            $token = $request->getHeader("token")[0];
    
            $obj_rta = Autentificadora::verificarJWT($token);
    
            $status = $obj_rta->verificado ? 200 : 403;
    
            $newResponse = $response->withStatus($status);
    
            $newResponse->getBody()->write(json_encode($obj_rta));
        
            return $newResponse->withHeader('Content-Type', 'application/json');
        }


        public function agregar_usuariosBD(): bool
        {
            $retorno = true;
    
            try {
    
                $objDB = connectDB::objAccess();
    
                $sql = $objDB->rtnSql("INSERT INTO `usuarios`(`correo`, `clave`, `nombre`, `apellido`, `foto`, `id_perfil`)"
                    . "VALUES (:correo,:clave,:nombre,:apellido,:foto; :id_perfil)");
    
    
                $sql->bindValue(':correo', $this->correo, PDO::PARAM_STR);
                $sql->bindValue(':clave', $this->clave, PDO::PARAM_INT);
                $sql->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
                $sql->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
                $sql->bindValue(':foto', $this->foto, PDO::PARAM_STR);
                $sql->bindValue(':id_perfil', $this->id_perfil, PDO::PARAM_INT);
                
    
                $sql->execute();

            } catch (PDOException) {
    
                $retorno = false;
            }
    
            return $retorno;
        }

        
        public static function traer_usuarios()
        {
    
            $query = connectDB::objAccess();
    
            $sql = $query->rtnSql("SELECT * FROM usuarios");
    
            $sql->execute();
    
            return $sql->fetchAll(PDO::FETCH_ASSOC);
        }


        

    }
}


?>