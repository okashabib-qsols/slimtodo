<?php
namespace App\Controllers;

use App\Models\Todo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TodoController
{
    public function index(Request $request, Response $response)
    {
        $todo = Todo::all();
        if ($todo) {
            $response->getBody()->write(json_encode(['success', true, 'message' => 'Got resource successfully', 'data' => $todo]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(['success', false, 'message' => 'Not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    }

    public function show(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        if (!is_numeric($id)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => "id must be number `$id is given` "]));
            return $response->withStatus(405)->withHeader('Content-Type', 'application/json');
        }
        $todo = Todo::find($id);
        if ($todo) {
            $todo = $response->getBody()->write(json_encode(['success', true, 'message' => 'Got resource successfully', 'data' => $todo]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Todo not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    }

    public function delete(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        if (!is_numeric($id)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => "id must be number `$id is given` "]));
            return $response->withStatus(405)->withHeader('Content-Type', 'application/json');
        }
        $todo = Todo::find($id);

        if ($todo) {
            $todo->delete();
            $response->getBody()->write(json_encode(['success' => true, 'message' => 'Deleted successfully']));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => "Todo not found with provided id $id"]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    }
}