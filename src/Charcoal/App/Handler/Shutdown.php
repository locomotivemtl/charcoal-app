<?php

namespace Charcoal\App\Handler;

// From PSR-7
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Slim
use Slim\Http\Body;

// From 'charcoal-app'
use Charcoal\App\Handler\AbstractHandler;

/**
 * Shutdown Handler
 *
 * It outputs a simple message in either JSON, XML, or HTML based on the Accept header.
 *
 * A maintenance mode check is included in the default middleware stack for your application.
 * This is a practical feature to "disable" your application while performing an update
 * or maintenance.
 */
class Shutdown extends AbstractHandler
{
    /**
     * HTTP methods allowed by the current request.
     *
     * @var string $methods
     */
    protected $methods;

    /**
     * Invoke "Maintenance" Handler
     *
     * @param  ServerRequestInterface $request  The most recent Request object.
     * @param  ResponseInterface      $response The most recent Response object.
     * @param  string[]               $methods  Allowed HTTP methods.
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $methods)
    {
        $this->setMethods($methods);

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
                default:
                    $output = $this->renderHtmlOutput();
                    break;
            }
        }

        $body = new Body(fopen('php://temp', 'r+'));
        $body->write($output);

        return $response
                ->withStatus(503)
                ->withHeader('Content-type', $contentType)
                ->withBody($body);
    }

    /**
     * Set the HTTP methods allowed by the current request.
     *
     * @param  array $methods Case-sensitive array of methods.
     * @return Shutdown Chainable
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
    public function methods()
    {
        return $this->methods;
    }

    /**
     * Render Plain/Text Error
     *
     * @return string
     */
    protected function renderPlainOutput()
    {
        $message = $this->translator()->translate('Down for maintenance!');

        return $this->render($message);
    }

    /**
     * Render JSON Error
     *
     * @return string
     */
    protected function renderJsonOutput()
    {
        $message = $this->translator()->translate('We are currently unavailable. Check back in 15 minutes.');

        return $this->render('{"message":"'.$message.'"}');
    }

    /**
     * Render XML Error
     *
     * @return string
     */
    protected function renderXmlOutput()
    {
        $message = $this->translator()->translate('We are currently unavailable. Check back in 15 minutes.');

        return $this->render('<root><message>'.$message.'</message></root>');
    }

    /**
     * Render title of error
     *
     * @return string
     */
    public function messageTitle()
    {
        return $this->translator()->translate('Down for maintenance!');
    }

    /**
     * Render body of HTML error
     *
     * @return string
     */
    public function renderHtmlMessage()
    {
        $title   = $this->messageTitle();
        $notice  = $this->translator()->translate('currently-unavailable');
        $message = '<h1>'.$title."</h1>\n\t\t<p>".$notice."</p>\n";

        return $message;
    }
}
