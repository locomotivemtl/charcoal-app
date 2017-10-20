<?php

namespace Charcoal\App\Handler;

use UnexpectedValueException;

// From PSR-7
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

// From 'charcoal-app'
use Charcoal\App\Handler\AbstractHandler;

/**
 * "Service Unavailable" Handler
 *
 * A maintenance mode check is included in the default middleware stack for your application.
 * This is a practical feature to "disable" your application while performing an update
 * or maintenance.
 */
class Maintenance extends AbstractHandler
{
    const DEFAULT_PARTIAL = 'charcoal/app/handler/503';

    /**
     * Invoke "Maintenance" Handler
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

        if ($request->getMethod() === 'OPTIONS') {
            $contentType = 'text/plain';
            $output = $this->renderPlainOutput();
        } else {
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
        }

        return $this->respondWith(
            $response->withStatus(503),
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
        $message = $this->translator()->translate('Service Unavailable', [], 'charcoal');

        return $this->renderTemplate($message);
    }

    /**
     * Render JSON Error
     *
     * @return string
     */
    protected function renderJsonOutput()
    {
        $message = $this->translator()->translate(
            'The server is currently unavailable. We will be right back.',
            [],
            'charcoal'
        );
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
        $message = $this->translator()->translate(
            'The server is currently unavailable. We will be right back.',
            [],
            'charcoal'
        );

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
        return 503;
    }

    /**
     * Retrieve the handler's summary.
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->translator()->translate('Service Unavailable', [], 'charcoal');
    }

    /**
     * Retrieve the handler's message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->translator()->translate(
            'The server is currently unavailable. We will be right back.',
            [],
            'charcoal'
        );
    }
}
