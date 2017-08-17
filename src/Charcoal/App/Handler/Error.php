<?php

namespace Charcoal\App\Handler;

use Exception;
use UnexpectedValueException;

// From PSR-7
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

// From 'charcoal-app'
use Charcoal\App\Handler\AbstractError;

/**
 * Error Handler
 *
 * Enhanced version of {@see \Slim\Handlers\NotFound}.
 */
class Error extends AbstractError
{
    const DEFAULT_PARTIAL = 'charcoal/app/handler/500';

    /**
     * Invoke Error Handler
     *
     * @param  ServerRequestInterface $request  The most recent Request object.
     * @param  ResponseInterface      $response The most recent Response object.
     * @param  Exception              $error    The caught Exception object.
     * @throws UnexpectedValueException If the content type could not be determined.
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        Exception $error
    ) {
        $this->setHttpRequest($request);
        $this->setThrown($error);

        $contentType = $this->determineContentType($request);
        switch ($contentType) {
            case 'application/json':
                $output = $this->renderJsonOutput();
                break;

            case 'text/xml':
            case 'application/xml':
                $output = $this->renderXmlOutput();
                break;

            case 'text/html':
                $output = $this->renderHtmlOutput();
                break;

            case 'text/plain':
                $output = $this->renderPlainOutput();
                break;

            default:
                throw new UnexpectedValueException(sprintf(
                    'Cannot render unknown content type: %s',
                    $contentType
                ));
        }

        $this->writeToErrorLog($error);

        return $this->respondWith(
            $response->withStatus(500),
            $contentType,
            $output
        );
    }

    /**
     * Render Text Error
     *
     * @return string
     */
    protected function renderPlainOutput()
    {
        $message = $this->renderTextMessage($this->getThrown());

        return $this->renderTemplate($message);
    }

    /**
     * Render JSON Error
     *
     * @return string
     */
    protected function renderJsonOutput()
    {
        $message = $this->renderJsonMessage($this->getThrown());

        return $this->renderTemplate($message);
    }

    /**
     * Render XML Error
     *
     * @return string
     */
    protected function renderXmlOutput()
    {
        $message = $this->renderXmlMessage($this->getThrown());

        return $this->renderTemplate($message);
    }

    /**
     * Render HTML Error
     *
     * @return string
     */
    protected function renderHtmlOutput()
    {
        return $this->renderHtmlTemplate();
    }
}
