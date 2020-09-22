<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Service;

use AVAllAC\PersistentBlockStorage\Exception\UnauthorizedException;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Throwable;

class Kernel
{
    protected $router;
    protected $statWriter;
    protected $username;
    protected $password;
    protected $logger;

    /**
     * Kernel constructor.
     * @param UrlMatcherInterface $router
     * @param StatWriter $statWriter
     * @param Logger $logger
     * @param null|string $username
     * @param null|string $password
     */
    public function __construct(
        UrlMatcherInterface $router,
        StatWriter $statWriter,
        Logger $logger,
        ?string $username,
        ?string $password
    ) {
        $this->router = $router;
        $this->statWriter = $statWriter;
        $this->logger = $logger;
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
     * @return Response
     * @throws UnauthorizedException
     */
    public function route(ServerRequestInterface $request) : Response
    {
        $authString = $request->getHeader('authorization')[0] ?? null;
        if ($authString === $this->authEncode()) {
            $params = ['request' => $request];
            $route = $request->getUri()->getPath();
            $matched = $this->router->match($route);
            $params = array_merge($params, $matched);
            $this->logger->debug(
                'Kernel',
                'resolve route',
                [
                    '_controller' => $params['_controller'],
                ]
            );
            unset($params['_controller']);
            unset($params['_route']);
            return call_user_func_array($matched['_controller'], $params);
        } else {
            throw new UnauthorizedException();
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return Response
     */
    public function handle(ServerRequestInterface $request) : Response
    {
        try {
            $this->logger->debug(
                'Kernel',
                'input request',
                [
                    'path' => $request->getUri()->getPath(),
                    'method' => $request->getMethod(),
                    'port' => $request->getUri()->getPort()
                ]
            );
            $response = $this->route($request);
            $this->statWriter->addAction($response->getStatusCode());
            return $response;
        } catch (InvalidParameterException $e) {
            $this->statWriter->addAction(400);
            return new Response(400, ['Content-Type' => 'text/plain'], $e->getMessage());
        } catch (UnauthorizedException $e) {
            $this->statWriter->addAction(401);
            return new Response(401, ['Content-Type' => 'text/plain'], '401 Unauthorized');
        } catch (ResourceNotFoundException $e) {
            $this->statWriter->addAction(404);
            return new Response(404, ['Content-Type' => 'text/plain'], $e->getMessage());
        } catch (Throwable $e) {
            $this->logger->error(
                'Kernel',
                'Unhandled exception',
                [
                    'class' => get_class($e),
                    'message' => $e->getMessage(),
                    'trace' =>  $e->getTraceAsString()
                ]
            );
            $this->statWriter->addAction(503);
            return new Response(503, ['Content-Type' => 'text/plain'], 'error');
        }
    }
}