<?php

namespace Charcoal\App\Handler;

use UnexpectedValueException;

// From PSR-7
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

// From 'charcoal-app'
use Charcoal\App\Handler\AbstractHandler;

/**
 * "Not Found" Handler
 *
 * Enhanced version of {@see \Slim\Handlers\NotFound}.
 */
class NotFound extends AbstractHandler
{
    const DEFAULT_PARTIAL = 'charcoal/app/handler/404';

    /**
     * Invoke "Not Found" Handler
     *
     * @param  ServerRequestInterface $request  The most recent Request object.
     * @param  ResponseInterface      $response The most recent Response object.
     * @throws UnexpectedValueException If the content type could not be determined.
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) {
        $this->setHttpRequest($request);

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

        return $this->respondWith(
            $response->withStatus(404),
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
        $message = $this->translator()->translate('Not Found', [], 'charcoal');

        return $this->renderTemplate($message);
    }

    /**
     * Render JSON Error
     *
     * @return string
     */
    protected function renderJsonOutput()
    {
        $message = $this->translator()->translate('Not Found', [], 'charcoal');
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

        return $this->renderTemplate('{"message":"'.$message.'"}');
    }

    /**
     * Render XML Error
     *
     * @return string
     */
    protected function renderXmlOutput()
    {
        $message = $this->translator()->translate('Not Found', [], 'charcoal');

        return $this->renderTemplate('<root><message>'.$message.'</message></root>');
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

    /**
     * Retrieve the response's HTTP code.
     *
     * @return integer
     */
    public function getCode()
    {
        return 404;
    }

    /**
     * Retrieve the handler's summary.
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->translator()->translate('Page Not Found', [], 'charcoal');
    }

    /**
     * Retrieve the handler's message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->translator()->translate(
            'The page you are looking for could not be found.',
            [],
            'charcoal'
        );
    }
}
