<?php
namespace App\Controllers;

use App\Models\Todo;
use Illuminate\Pagination\Paginator;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Csrf\Guard;
use Slim\Views\Twig;
class TodoController
{
    protected $view;
    protected $csrf;
    protected $logger;
    public function __construct(Twig $view, Guard $csrf, Logger $logger)
    {
        $this->view = $view;
        $this->csrf = $csrf;
        $this->logger = $logger;
    }

    private function getCsrfTokens()
    {
        $csrfNameKey = $this->csrf->getTokenNameKey();
        $csrfValueKey = $this->csrf->getTokenValueKey();
        $csrfName = $this->csrf->getTokenName();
        $csrfValue = $this->csrf->getTokenValue();

        return [
            'csrf' => [
                'keys' => [
                    'name' => $csrfNameKey,
                    'value' => $csrfValueKey
                ],
                'name' => $csrfName,
                'value' => $csrfValue
            ]
        ];
    }

    public function index(Request $request, Response $response)
    {
        try {
            $this->logger->info("Fetching Todos");

            Paginator::currentPageResolver(function () use ($request) {
                return (int) ($request->getQueryParams()['page'] ?? 1);
            });

            $todos = Todo::orderBy('item_position')->paginate(10);

            if (!$todos->isEmpty()) {
                $this->logger->info('Successfully fetched Todos');

                return $this->view->render($response, 'todo.html.twig', array_merge(
                    [
                        'success' => true,
                        'message' => 'Got resource successfully',
                        'data' => $todos,
                        'pagination' => [
                            'currentPage' => $todos->currentPage(),
                            'lastPage' => $todos->lastPage(),
                            'nextPageUrl' => $todos->nextPageUrl(),
                            'previousPageUrl' => $todos->previousPageUrl(),
                        ],
                        'title' => "Todo App"
                    ],
                    $this->getCsrfTokens()
                ));
            } else {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Data not found'
                ]));
                $this->logger->error("Error while fetching todos");
                return $response
                    ->withHeader('Content-Type', 'application/json');
            }
        } catch (\Exception $e) {
            $this->logger->error('Exception while fetching todos. Error: ' . $e->getMessage());
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'An error occurred while fetching todos.',
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function show(Request $request, Response $response, $args)
    {
        try {
            $id = $args['id'];
            if (!is_numeric($id)) {
                $this->logger->warning("Invalid ID format: $id is not numeric");
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => "id must be number `$id is given` "
                ]));
                return $response
                    ->withStatus(400)
                    ->withHeader('Content-Type', 'application/json');
            }
            $todo = Todo::find($id);
            if ($todo) {
                $this->logger->info("Todo found with ID: $id");
                $todo = $response->getBody()
                    ->write(json_encode(array_merge(
                        [
                            'success' => true,
                            'message' => 'Got resource successfully',
                            'data' => $todo
                        ],
                        $this->getCsrfTokens()
                    )));
                return $response
                    ->withStatus(200)
                    ->withHeader('Content-Type', 'application/json');
            } else {
                $this->logger->warning("Todo not found with ID: $id");
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Todo not found'
                ]));
                return $response
                    ->withStatus(404)
                    ->withHeader('Content-Type', 'application/json');
            }
        } catch (\Exception $e) {
            $this->logger->error("Exception while showing todo with id $id Error: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'An error occurred while showing todos.',
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function store(Request $request, Response $response, $args)
    {
        try {
            $form_data = $request->getParsedBody();
            if (trim($form_data['description']) == "") {
                $response->getBody()->write(json_encode(array_merge(
                    [
                        'success' => false,
                        'message' => 'Description is required',
                    ],
                    $this->getCsrfTokens()
                )));
                $this->logger->warning('Description is required');
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            $item_position = Todo::max('item_position');
            $position = $item_position + 1;
            $todo = Todo::create([
                'description' => $form_data['description'],
                'is_done' => 0,
                'item_position' => $position,
                'color' => 1
            ]);

            $response->getBody()->write(json_encode(array_merge(
                [
                    'success' => true,
                    'message' => 'Todo added',
                    'data' => $todo,
                ],
                $this->getCsrfTokens()
            )));
            $this->logger->info("Successfully added todo", ['todo' => $todo]);
            return $response
                ->withStatus(201)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $this->logger->error("Exception while storing todo. Error: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'An error occurred while storing todos.',
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function update(Request $request, Response $response, $args)
    {
        try {
            $id = $args['id'];
            if (!is_numeric($id)) {
                $this->logger->warning("Invalid ID format: $id is not numeric");
                $response
                    ->getBody()
                    ->write(json_encode([
                        'success' => false,
                        'message' => "id must be number `$id is given` "
                    ]));
                return $response
                    ->withStatus(400)
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
                if (isset($data['is_done'])) {
                    $todo->is_done = $data['is_done'];
                }

                $todo->save();

                $response->getBody()->write(json_encode(array_merge(
                    [
                        'success' => true,
                        'message' => 'Todo updated successfully.',
                        'data' => $todo,
                    ],
                    $this->getCsrfTokens()
                )));
                $this->logger->info("Updated todo with id: $id", ['data' => $data]);
                return $response
                    ->withStatus(200)
                    ->withHeader('Content-Type', 'application/json');
            } else {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Todo not found.'
                ]));
                $this->logger->error("Todo not found with id: $id");
                return $response
                    ->withStatus(404)
                    ->withHeader('Content-Type', 'application/json');
            }
        } catch (\Exception $e) {
            $this->logger->error("Exception while updating todo with ID: $id Error: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'An error occurred while updating todos.',
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function update_position(Request $request, Response $response)
    {
        try {
            $data = $request->getParsedBody();
            $this->logger->info("Received request to update positions.", ['data' => $data]);
            if (isset($data['position'])) {
                foreach ($data['position'] as $item_positions) {
                    $this->logger->info("Updating position for Todo ID: {$item_positions['id']}", ['new_position' => $item_positions['position']]);
                    $todo = Todo::find($item_positions['id']);
                    if ($todo) {
                        $todo->item_position = $item_positions['position'];
                        $todo->save();
                        $this->logger->info("Updated position for Todo ID: {$item_positions['id']} successfully.", ['updated_position' => $item_positions['position']]);
                    } else {
                        $this->logger->warning("Todo ID: {$item_positions['id']} not found for position update.");
                    }
                }

                $response->getBody()->write(json_encode(array_merge(
                    [
                        'success' => true,
                        'message' => 'Item Positions updated successfully.'
                    ],
                    $this->getCsrfTokens()
                )));
                $this->logger->info("Item Positions updated successfully, sending response.");
                return $response
                    ->withStatus(200)
                    ->withHeader('Content-Type', 'application/json');
            } else {
                $this->logger->warning("No item positions provided in the request.");
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'No item positions provided.'
                ]));
                return $response->withHeader('Content-Type', 'application/json');
            }
        } catch (\Exception $e) {
            $this->logger->error("Exception while updating positions todos. Error: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'An error occurred while updating positions todos.',
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function delete(Request $request, Response $response, $args)
    {
        try {
            $id = $args['id'];
            if (!is_numeric($id)) {
                $this->logger->warning("Invalid ID format: $id is not numeric");
                $response
                    ->getBody()
                    ->write(json_encode(['success' => false, 'message' => "id must be number `$id is given` "]));
                return $response
                    ->withStatus(400)
                    ->withHeader('Content-Type', 'application/json');
            }
            $todo = Todo::find($id);

            if ($todo) {
                $this->logger->info("Todo found with ID: $id. Proceeding with deletion.");
                $todo->delete();
                $response->getBody()
                    ->write(json_encode(array_merge(
                        [
                            'success' => true,
                            'message' => 'Deleted successfully'
                        ],
                        $this->getCsrfTokens()
                    )));
                return $response
                    ->withStatus(200)
                    ->withHeader('Content-Type', 'application/json');
            } else {
                $this->logger->warning("Todo not found with ID: $id. Deletion failed.");
                $response->getBody()
                    ->write(json_encode([
                        'success' => false,
                        'message' => "Todo not found with provided id $id"
                    ]));
                return $response
                    ->withStatus(404)
                    ->withHeader('Content-Type', 'application/json');
            }
        } catch (\Exception $e) {
            $this->logger->error("Exception while deleting todo with ID: $id Error: " . $e->getMessage());
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'An error occurred while deleting todos.',
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}