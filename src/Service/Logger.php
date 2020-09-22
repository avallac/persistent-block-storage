<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Service;

use Monolog\Logger as MLogger;
use Monolog\Handler\HandlerInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

class Logger
{
    const NO_LOGS = 0;
    const FATAL = 1;
    const ERROR = 2;
    const WARN = 3;
    const INFO = 4;
    const DEBUG = 5;

    /** @var MLogger[] */
    protected $loggers = [];
    protected $level;

    public function __construct(int $level)
    {
        $this->level = $level;
        $this->loggers['console'] = new MLogger('console');
    }

    public function error(string $name, $message, array $context = array())
    {
        if (!is_string($message)) {
            $message = var_export($message, true);
        }
        if (!empty($this->loggers[$name])) {
            if (!empty($this->loggers[$name]->getHandlers())) {
                $this->loggers[$name]->error($message, array_merge($context, ['channel' => $name]));
            }
        }
        if ($this->level >= self::ERROR) {
            if (!empty($this->loggers['console']->getHandlers())) {
                $this->loggers['console']->error($message, array_merge($context, ['channel' => $name]));
            }
        }
    }

    public function debug(string $name, $message, array $context = array())
    {
        if (!is_string($message)) {
            $message = var_export($message, true);
        }
        if (!empty($this->loggers[$name])) {
            if (!empty($this->loggers[$name]->getHandlers())) {
                $this->loggers[$name]->debug($message, array_merge($context, ['channel' => $name]));
            }
        }
        if ($this->level === self::DEBUG) {
            if (!empty($this->loggers['console']->getHandlers())) {
                $this->loggers['console']->debug($message, array_merge($context, ['channel' => $name]));
            }
        }
    }

    public function info(string $name, $message, array $context = array())
    {
        if (!is_string($message)) {
            $message = var_export($message, true);
        }
        if (!empty($this->loggers[$name])) {
            if (!empty($this->loggers[$name]->getHandlers())) {
                $this->loggers[$name]->info($message, array_merge($context, ['channel' => $name]));
            }
        }
        if ($this->level >= self::INFO) {
            if (!empty($this->loggers['console']->getHandlers())) {
                $this->loggers['console']->info($message, array_merge($context, ['channel' => $name]));
            }
        }
    }

    public function pushHandler(string $name, HandlerInterface $handler)
    {
        if (!empty($this->loggers[$name])) {
            $this->loggers[$name]->pushHandler($handler);
        }
    }

    public function initConsoleOutput()
    {
        $output = '[' . "\033[0;36m" . '%datetime%' . "\033[0m" . "] %channel%.%level_name%: %message% %context%\n";
        $formatter = new LineFormatter($output);

        $streamHandler = new StreamHandler('php://stdout', MLogger::DEBUG);
        $streamHandler->setFormatter($formatter);

        $this->pushHandler('console', $streamHandler);
    }
}
