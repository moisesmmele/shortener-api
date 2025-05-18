<?php

namespace Moises\ShortenerApi\Application;

use Symfony\Component\HttpFoundation\Request;

class Router
{
    private array $routes = [];

    public function __construct()
    {
        $this->loadRoutes();
    }

    /**
     * Register a route with HTTP method, URI, and action (callable or controller+method array)
     */
    public function register(string $method, string $uri, array|callable $action): self
    {
        if (is_callable($action)) {
            $this->routes[] = [
                'method' => strtoupper($method),
                'uri' => $uri,
                'action' => $action
            ];
        }

        if (is_array($action)) {
            $this->routes[] = [
                'method' => strtoupper($method),
                'uri' => $uri,
                'action' => [
                    'controller' => $action[0],
                    'method' => $action[1],
                ]
            ];
        }

        return $this;
    }

    /**
     * Handle incoming HTTP request and match it to a registered route
     */
    public function dispatch()
    {
        $request = Request::createFromGlobals(); // Create a Request object from current HTTP globals

        $requestUri = $request->getPathInfo(); // Extract URI path
        $requestMethod = $request->getMethod(); // Extract HTTP method

        error_log("INFO: [$requestMethod] ......................... [$requestUri]");

        foreach ($this->routes as $route) {
            // Skip if request method does not match
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            // Convert route URI pattern to regex, e.g., /user/:id becomes /user/(?P<id>[^/]+)
            $pattern = preg_replace('#:([\w]+)#', '(?P<$1>[^/]+)', $route['uri']);
            $pattern = "#^" . $pattern . "$#";

            // Check if request URI matches pattern
            if (preg_match($pattern, $requestUri, $matches)) {
                // Remove numeric keys from $matches array
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // If action is a closure, invoke it directly
                if (is_callable($route['action'])) {
                    call_user_func($route['action'], $request, $params);
                    return;
                }

                // If action is controller/method, instantiate and call
                if (is_array($route['action'])) {
                    $controller = $route['action']['controller'];
                    $method = $route['action']['method'];
                    (new $controller)->$method($request, $params);
                    return;
                }
            }
        }

        // If no route matched, fallback to default service handler
        $service = new ServiceHub();
        $service->handle($request);
    }

    /**
     * Load route definitions from external file
     */
    public function loadRoutes()
    {
        (require BASE_PATH . "/config/routes.php")($this);
    }
}
