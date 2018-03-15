<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Service;

use AVAllAC\PersistentBlockStorage\Exception\UnauthorizedException;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

class Kernel
{
    private $router;
    private $username;
    private $password;

    public function __construct(UrlMatcherInterface $router, string $username, string $password)
    {
        $this->router = $router;
        $this->username = $username;
        $this->password = $password;
    }

    protected function authEncode()
    {
        return null;
        return 'Basic ' . base64_encode($this->username . ':' . $this->password);
    }

    public function route(ServerRequestInterface $request) : string
    {
        $authString = $this->authEncode();
        if ($authString === $request->getHeader('authorization')[0]) {
            $params = ['request' => $request];
            $route = $request->getUri()->getPath();
            $matched = $this->router->match($route);
            $params = array_merge($params, $matched);
            unset($params['_controller']);
            unset($params['_route']);
            return \call_user_func_array($matched['_controller'], $params);
        } else {
            throw new UnauthorizedException();
        }
    }

    public function handle(ServerRequestInterface $request) : Response
    {
        try {
            $response = $this->route($request);
            return new Response(200, ['Content-Type' => 'text/plain'], $response);
        } catch (InvalidParameterException $e) {
            return new Response(400, ['Content-Type' => 'text/plain'], $e->getMessage());
        } catch (UnauthorizedException $e) {
            return new Response(401, ['Content-Type' => 'text/plain'], '401 Unauthorized');
        } catch (ResourceNotFoundException $e) {
            return new Response(404, ['Content-Type' => 'text/plain'], $e->getMessage());
        } catch (\Throwable $e) {
            return new Response(503, ['Content-Type' => 'text/plain'], $e->getMessage());
        }
    }
}