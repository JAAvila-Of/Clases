<?php

namespace jaavila\graph_ql;

require __DIR__ . '/../../guzzle/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;

/**
 * Esta clase permite conectar distintas funciones a GraphQL utilizando GuzzleHttp
 * 
 * Cabe resaltar que no presenta método constructor con el fin de procesar todo a
 * travez de las mismas funciones
 */
class graph
{
    /**
     * @var string $graph_ta Esta variable guarda el Token de entrada que será enviada a GraphQL por GET
     */
    protected $graph_ta;

    /**
     * @var string $graph_tk Esta variable guarda el Token de validación primaria que será enviada a GrapQL por GET
     */
    protected $graph_tk;

    protected $graph_sess;

    /**
     * @var string $query Esta variable almacena la cadena de la query que se envia a GraphQL utilizando Guzzle por POST
     */
    protected $query;


    /**
     * Esta función envia la Query utilizando Guzzle a GraphQL
     * 
     * La Query se desarrolla con las funciones siguientes en formato string por lo que se convierte a json para el envío
     * 
     * @return StreamInterface|string Retorna el valor resultante del POST con Guzzle 
     */
    protected function Send()
    {
        try {
            $client = new Client(['base_uri' => 'http://localhost/contab/']);
            $request = $client->post(
                "graphql/index.php?sec={$this->graph_ta}&sess={$this->graph_sess}",
                [
                    'body' => json_encode(["query" => $this->query])
                ]
            );
        } catch (RequestException $e) {
            //echo Psr7\str($e->getRequest());
            if ($e->hasResponse()) {
                return Psr7\str($e->getResponse());
            } else {
                return 'Error en la conexión de GraphQL';
            }
        }

        if ($request->getStatusCode() == 200) {
            $body = $request->getBody();
        } else {
            $body = 'Error en la respuesta desde el servidor: ' . $request->getStatusCode();
        }
        return $body;
    }

    public function query()
    {
        $this->query = "query {" . PHP_EOL;
        return $this;
    }

    public function mutation()
    {
        $this->query = "mutation {" . PHP_EOL;
        return $this;
    }

    public function funcion(string $funcion)
    {
        $this->query .= $funcion . " (" . PHP_EOL;
        return $this;
    }

    public function args(array $args = [], string $key = null, $content = null, bool $last = false)
    {
        if (!empty($args)) {
            foreach ($args as $keys => $value) {
                $v = $this->typeValue($value);
                $this->query .= $keys . ":" . $v . "," . PHP_EOL;
            }
            $this->query .= ")";
            return $this;
        }

        if ($key != null && $content != null) {
            $v = $this->typeValue($content);
            $this->query .= $key . ":" . $v . "," . PHP_EOL;
        } else {
            return false;
        }

        if ($last) {
            $this->query .= ")";
        }

        return $this;
    }

    public function response(array $res = [], string $r = null, bool $last = false)
    {
        if (!empty($res)) {
            $this->query .= "{" . PHP_EOL;
            foreach ($res as $value) {
                $this->query .= $value . PHP_EOL;
            }
            $this->query .= "}" . PHP_EOL;
            return $this;
        }

        if ($r != null) {
            $this->query .= $r . PHP_EOL;
        } else {
            return false;
        }

        if ($last) {
            $this->query .= "}" . PHP_EOL;
        }

        return $this;
    }

    public function end()
    {
        $this->query .= "}";
        return $this;
    }

    protected function typeValue($v)
    {
        if (is_string($v)) {
            $r = '"' . $v . '"';
        } else if (is_bool($v)) {
            $r = $v ? "true" : "false";
        } else {
            $r = $v;
        }

        return $r;
    }

    public function sendQuery(int $ta, string $sess)
    {
        $this->graph_ta = $ta;
        $this->graph_sess = $sess;
        return $this->Send(); //$this->query; //$this->Send();

    }
}
