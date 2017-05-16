<?php

namespace LearnositySdk\Utils;

use \Exception;
use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\StreamHandler;
use \Monolog\Handler\SyslogHandler;
use \Monolog\Handler\ErrorLogHandler;

/**
 *--------------------------------------------------------------------------
 * Learnosity SDK - Log
 *--------------------------------------------------------------------------
 *
 * Used to log the (optional) following types:
 *     request_response
 *     summary
 *     errror
 *
 * Can be logged using the following handlers:
 *     file
 *     syslog
 *     stdout
 */

class Log
{
    private $enabled;
    private $basePath;
    private $RequestResponseLogger;
    private $SummaryLogger;
    private $ErrorLogger;
    private $SyslogLogger;
    private $StdOutLogger;
    private $fileValueTypesEnabled = array();
    private $syslogValueTypesEnabled = array();
    private $stdOutValueTypesEnabled = array();
    private $files = array(
        'request_response' => 'dataapi-request_response.log',
        'summary'          => 'dataapi-summary.log',
        'error'            => 'dataapi-error.log'
    );

    /**
     * @param string $logOptions User defined logging options
     */
    public function __construct($logOptions = null)
    {
        $this->validateLogOptions($logOptions);
        $this->setupLogHandlers();
    }

    /**
     * Writes a value to a set of handlers that
     * have been setup. String and simple array
     * values only
     */
    public function write($type, $value)
    {
        if ($this->loggingEnabled()) {
            try {
                // Checks for simple arrays only
                if (is_array($value)) {
                    $value = implode(',', $value);
                }

                switch ($type) {
                    case 'request_response':
                        if (in_array('request_response', $this->fileValueTypesEnabled)) {
                            $this->RequestResponseLogger->addInfo($value);
                        }
                        if (in_array('request_response', $this->syslogValueTypesEnabled)) {
                            $this->SyslogLogger->addInfo($value);
                        }
                        if (in_array('request_response', $this->stdOutValueTypesEnabled)) {
                            $this->StdOutLogger->addInfo($value);
                        }
                        break;

                    case 'summary':
                        if (in_array('summary', $this->fileValueTypesEnabled)) {
                            $this->SummaryLogger->addInfo($value);
                        }
                        if (in_array('summary', $this->syslogValueTypesEnabled)) {
                            $this->SyslogLogger->addInfo($value);
                        }
                        if (in_array('summary', $this->stdOutValueTypesEnabled)) {
                            $this->StdOutLogger->addInfo($value);
                        }
                        break;

                    case 'error':
                        if (in_array('error', $this->fileValueTypesEnabled)) {
                            $this->ErrorLogger->addInfo($value);
                        }
                        if (in_array('error', $this->syslogValueTypesEnabled)) {
                            $this->SyslogLogger->addInfo($value);
                        }
                        if (in_array('error', $this->stdOutValueTypesEnabled)) {
                            $this->StdOutLogger->addInfo($value);
                        }
                        break;

                    default:
                        break;
                }
            } catch (Exception $e) {
                die($e->__toString());
            }
        }
    }

    public function loggingEnabled()
    {
        return $this->enabled;
    }

    private function validateLogOptions($logOptions)
    {
        if (empty($logOptions) || (!isset($logOptions['handlers']) || empty($logOptions['handlers']))) {
            $this->enabled = false;
        } else {
            // Check file logger
            if (isset($logOptions['handlers']['file']['path']) && !empty($logOptions['handlers']['file']['path'])) {
                $this->basePath = rtrim($logOptions['handlers']['file']['path'], '/') . '/';
                $this->checkPathIsWritable();
                if (isset($logOptions['handlers']['file']['types']) && is_array($logOptions['handlers']['file']['types']) && !empty($logOptions['handlers']['file']['types'])) {
                    $this->fileValueTypesEnabled = $logOptions['handlers']['file']['types'];
                    $this->enabled = true;
                }
            }

            // Check syslog logger
            if (isset($logOptions['handlers']['syslog']['types']) && is_array($logOptions['handlers']['syslog']['types']) && !empty($logOptions['handlers']['syslog']['types'])) {
                $this->syslogValueTypesEnabled = $logOptions['handlers']['syslog']['types'];
                $this->enabled = true;
            }

            // Check syslog logger
            if (isset($logOptions['handlers']['stdout']['types']) && is_array($logOptions['handlers']['stdout']['types']) && !empty($logOptions['handlers']['stdout']['types'])) {
                $this->stdOutValueTypesEnabled = $logOptions['handlers']['stdout']['types'];
                $this->enabled = true;
            }
        }
    }

    /*
     * Checks that the given file log path is writable.
     * If it is, create log files in preparation of
     * writing values if they don't already exist.
     */
    private function checkPathIsWritable()
    {
        if (is_dir($this->basePath) && is_writable($this->basePath)) {
            foreach ($this->files as $name => $file) {
                if (!file_exists($this->basePath . $file)) {
                    touch($this->basePath . $file);
                }
            }
        } else {
            throw new Exception('Cannot write logs to ' . $this->basePath);
        }
    }

    /*
     * Set up logging handlers based off log options
     */
    private function setupLogHandlers()
    {
        if ($this->loggingEnabled()) {
            $format = "[%datetime%] %message%\n";
            $formatter = new LineFormatter($format, null, false, true);

            if (!empty($this->fileValueTypesEnabled)) {
                if (in_array('request_response', $this->fileValueTypesEnabled)) {
                    $this->RequestResponseLogger = new Logger('request_response');
                    $stream = new StreamHandler($this->files['request_response'], Logger::INFO);
                    $this->RequestResponseLogger->pushHandler($stream->setFormatter($formatter));
                }

                if (in_array('summary', $this->fileValueTypesEnabled)) {
                    $this->SummaryLogger = new Logger('summary');
                    $stream = new StreamHandler($this->files['summary'], Logger::INFO);
                    $this->SummaryLogger->pushHandler($stream->setFormatter($formatter));
                }

                if (in_array('error', $this->fileValueTypesEnabled)) {
                    $this->ErrorLogger = new Logger('error');
                    $stream = new StreamHandler($this->files['error'], Logger::INFO);
                    $this->ErrorLogger->pushHandler($stream->setFormatter($formatter));
                }
            }

            if (!empty($this->syslogValueTypesEnabled)) {
                $this->SyslogLogger = new Logger('syslog');
                $stream = new SyslogHandler('syslogger');
                $this->SyslogLogger->pushHandler($stream->setFormatter($formatter));
            }

            if (!empty($this->stdOutValueTypesEnabled)) {
                $this->StdOutLogger = new Logger('stdout');
                $stream = new StreamHandler('php://stdout', Logger::INFO);
                $this->StdOutLogger->pushHandler($stream->setFormatter($formatter));
            }
        }
    }
}
