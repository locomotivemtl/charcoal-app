<?php

namespace Charcoal\App\Handler;

use UnexpectedValueException;

// From PSR-7
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

// From 'charcoal-app'
use Charcoal\App\Handler\AbstractHandler;

/**
 * "Not Allowed" Handler
 *
 * Enhanced version of {@see \Slim\Handlers\NotAllowed}.
 */
class NotAllowed extends AbstractHandler
{
    const DEFAULT_PARTIAL = 'charcoal/app/handler/405';

    /**
     * HTTP methods allowed by the current request.
     *
     * @var string $methods
     */
    protected $methods;

    /**
     * Invoke "Not Allowed" Handler
     *
     * @param  ServerRequestInterface $request  The most recent Request object.
     * @param  ResponseInterface      $response The most recent Response object.
     * @param  string[]               $methods  Allowed HTTP methods.
     * @throws UnexpectedValueException If the content type could not be determined.
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $methods
    ) {
        $this->setHttpRequest($request);
        $this->setMethods($methods);

        if ($request->getMethod() === 'OPTIONS') {
            $status = 200;
            $contentType = 'text/plain';
            $output = $this->renderPlainOutput();
        } else {
            $status = 405;
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
            $response->withStatus($status)
                     ->withHeader('Allow', $this->getMethods()),
            $contentType,
            $output
        );
    }

    /**
     * Set the HTTP methods allowed by the current request.
     *
     * @param  array $methods Case-sensitive array of methods.
     * @return self
     */
    protected function setMethods(array $methods)
    {
        $this->methods = implode(', ', $methods);

        return $this;
    }

    /**
     * Retrieves the HTTP methods allowed by the current request.
     *
     * @return string Returns the allowed request methods.
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Render Text Error
     *
     * @return string
     */
    protected function renderPlainOutput()
    {
        $message = $this->translator()->translate('Allowed methods: {{ methods }}', [
            '{{ methods }}' => $this->getMethods()
        ], 'charcoal');

        return $this->renderTemplate($message);
    }

    /**
     * Render JSON Error
     *
     * @return string
     */
    protected function renderJsonOutput()
    {
        $message = $this->translator()->translate('Method not allowed. Must be one of: {{ methods }}', [
            '{{ methods }}' => $this->getMethods()
        ], 'charcoal');
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
        $message = $this->translator()->translate('Method not allowed. Must be one of: {{ methods }}', [
            '{{ methods }}' => $this->getMethods()
        ], 'charcoal');

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
     * Prepare the template data for rendering.
     *
     * @param  array|\ArrayAccess $data Raw template data.
     * @return array|\ArrayAccess Expanded and processed template data.
     */
    protected function parseTemplateData($data = [])
    {
        $data['allowedMethods'] = $this->getMethods();

        return parent::parseTemplateData($data);
    }

    /**
     * Retrieve the response's HTTP code.
     *
     * @return integer
     */
    public function getCode()
    {
        return 405;
    }

    /**
     * Retrieve the handler's summary.
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->translator()->translate('Method not allowed.', [], 'charcoal');
    }

    /**
     * Retrieve the handler's message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->renderPlainOutput();
    }
}
