<?php
namespace App\Controllers;

use Psr\Container\ContainerInterface;
use App\Models\Todo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TodoController
{
    protected $container;
    protected $view;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->view = $container->get("view");
    }

    public function index(Request $request, Response $response)
    {
        $todo = Todo::orderBy('item_position')->get();
        if ($todo) {
            return $this->view->render($response, 'todo.html.twig', [
                'success' => true,
                'message' => 'Got resource successfully',
                'data' => $todo
            ]);
        } else {
            $response
                ->getBody()
                ->write(json_encode(['success', false, 'message' => 'Data not found']));
            return $response
                ->withStatus(404)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public function show(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        if (!is_numeric($id)) {
            $response->getBody()->write(json_encode(['success' => false, 'message' => "id must be number `$id is given` "]));
            return $response
                ->withStatus(405)
                ->withHeader('Content-Type', 'application/json');
        }
        $todo = Todo::find($id);
        if ($todo) {
            $todo = $response->getBody()
                ->write(json_encode(['success', true, 'message' => 'Got resource successfully', 'data' => $todo]));
            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(['success' => false, 'message' => 'Todo not found']));
            return $response
                ->withStatus(404)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public function store(Request $request, Response $response, $args)
    {
        $form_data = $request->getParsedBody();

        $item_position = Todo::max('item_position');
        $position = $item_position + 1;
        $todo = Todo::create([
            'description' => $form_data['description'],
            'is_done' => 0,
            'item_position' => $position,
            'color' => 1
        ]);

        $response->getBody()->write(json_encode(['success' => true, 'message' => 'Todo added', 'data' => $todo]));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    public function update(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        if (!is_numeric($id)) {
            $response
                ->getBody()
                ->write(json_encode(['success' => false, 'message' => "id must be number `$id is given` "]));
            return $response
                ->withStatus(405)
                ->withHeader('Content-Type', 'application/json');
        }
        $todo = Todo::find($id);
        $data = $request->getParsedBody();

        if ($todo) {
            if (isset($data['description'])) {
                $todo->description = $data['description'];
            }
            if (isset($data['color'])) {
                $todo->color = $data['color'];
            }
            if (isset($data['item_positions'])) {
                foreach ($data['item_positions'] as $item_pos) {
                    $item = Todo::find($item_pos['id']);
                    if ($item) {
                        $todo->item_position = $item_pos['item_positions'];
                        $item->save();
                    }
                }
            }
            if (isset($data['is_done'])) {
                $todo->is_done = $data['is_done'];
            }

            $todo->save();

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Todo updated successfully.',
                'data' => $todo
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Todo not found.'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    }

    public function updateposition(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        if (isset($data['item_positions'])) {
            foreach ($data['item_positions'] as $item_pos) {
                $todo = Todo::find($item_pos['id']);
                if ($todo) {
                    $todo->item_position = $item_pos['position'];
                    $todo->save();
                }
            }

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Todos updated successfully.'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'No item positions provided.'
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    public function delete(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        if (!is_numeric($id)) {
            $response
                ->getBody()
                ->write(json_encode(['success' => false, 'message' => "id must be number `$id is given` "]));
            return $response
                ->withStatus(405)
                ->withHeader('Content-Type', 'application/json');
        }
        $todo = Todo::find($id);

        if ($todo) {
            $todo->delete();
            $response->getBody()
                ->write(json_encode(['success' => true, 'message' => 'Deleted successfully']));
            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()
                ->write(json_encode(['success' => false, 'message' => "Todo not found with provided id $id"]));
            return $response
                ->withStatus(404)
                ->withHeader('Content-Type', 'application/json');
        }
    }
}