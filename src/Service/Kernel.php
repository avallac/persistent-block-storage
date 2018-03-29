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

    /**
     * Kernel constructor.
     * @param UrlMatcherInterface $router
     * @param null|string $username
     * @param null|string $password
     */
    public function __construct(UrlMatcherInterface $router, ?string $username, ?string $password)
    {
        $this->router = $router;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return null|string
     */
    protected function authEncode() : ?string
    {
        if (isset($this->username) && isset($this->password)) {
            return 'Basic ' . base64_encode($this->username . ':' . $this->password);
        }
        return null;
    }

    /**
     * @param ServerRequestInterface $request
     * @return object
     * @throws UnauthorizedException
     */
    public function route(ServerRequestInterface $request) : object
    {
        $authString = $request->getHeader('authorization')[0] ?? null;
        if ($authString === $this->authEncode()) {
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

    /**
     * @param ServerRequestInterface $request
     * @return object
     */
    public function handle(ServerRequestInterface $request) : object
    {
        try {
            return $response = $this->route($request);
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