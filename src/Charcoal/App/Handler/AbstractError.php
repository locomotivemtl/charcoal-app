<?php

namespace Charcoal\App\Handler;

// From PSR-3
use Psr\Log\LoggerInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-app'
use Charcoal\App\Handler\AbstractHandler;

/**
 * Abstract Charcoal Application Error Handler
 *
 * Enhanced version Slim's error handler.
 *
 * It outputs messages in either JSON, XML or HTML
 * based on the Accept header.
 */
abstract class AbstractError extends AbstractHandler
{
    /**
     * Whether to output the error's details.
     *
     * @var boolean $displayErrorDetails
     */
    private $displayErrorDetails;

    /**
     * The caught throwable.
     *
     * @var \Exception|\Throwable
     */
    private $thrown;

    /**
     * Retrieves the HTTP methods allowed by the current request.
     *
     * @return boolean
     */
    public function displayErrorDetails()
    {
        return $this->displayErrorDetails;
    }

    /**
     * Determine if the handler has a thrown object.
     *
     * @return boolean
     */
    public function hasThrown()
    {
        return !!$this->thrown;
    }

    /**
     * Retrieves the thrown object.
     *
     * @return \Exception|\Throwable
     */
    public function getThrown()
    {
        return $this->thrown;
    }

    /**
     * Render the HTML error details.
     *
     * @return string
     */
    public function renderHtmlErrorDetails()
    {
        $error = $this->getThrown();

        $html = $this->renderHtmlError($error);
        while ($error = $error->getPrevious()) {
            $html .= '<h2>'.$this->translator()->translate('Previous error').'</h2>';
            $html .= $this->renderHtmlError($error);
        }

        return $html;
    }

    /**
     * Retrieve the response's HTTP code.
     *
     * @return integer
     */
    public function getCode()
    {
        return 500;
    }

    /**
     * Retrieve the handler's summary.
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->translator()->translate('Application Error', [], 'charcoal');
    }

    /**
     * Retrieve the handler's message.
     *
     * @return string
     */
    public function getMessage()
    {
        if ($this->displayErrorDetails() && $this->hasThrown()) {
            return $this->getThrown()->getMessage();
        } else {
            return $this->translator()->translate(
                'The server encountered a problem. Sorry for the temporary inconvenience.',
                [],
                'charcoal'
            );
        }
    }

    /**
     * Set dependencies from the service locator.
     *
     * @param  Container $container A service locator.
     * @return self
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $displayDetails = $container['settings']['displayErrorDetails'];
        $this->setDisplayErrorDetails($displayDetails);

        return $this;
    }

    /**
     * Set whether to display details of the error or a generic message.
     *
     * @param  boolean $state Whether to display error details.
     * @return self
     */
    protected function setDisplayErrorDetails($state)
    {
        $this->displayErrorDetails = !!$state;

        return $this;
    }



    /**
     * Set the thrown object.
     *
     * @param  \Exception|\Throwable $throwable The caught Throwable object.
     * @return self
     */
    protected function setThrown($throwable)
    {
        $this->thrown = $throwable;

        return $this;
    }

    /**
     * Render error as Text.
     *
     * @param  \Exception|\Throwable $throwable The caught Throwable object.
     * @return string
     */
    protected function renderThrowableAsText($throwable)
    {
        $text = sprintf('Type: %s'.PHP_EOL, get_class($throwable));

        $code = $throwable->getCode();
        if (!empty($code)) {
            $text .= sprintf('Code: %s'.PHP_EOL, $code);
        }

        $message = $throwable->getMessage();
        if (!empty($message)) {
            $text .= sprintf('Message: %s'.PHP_EOL, htmlentities($message));
        }

        $file = $throwable->getFile();
        if (!empty($file)) {
            $text .= sprintf('File: %s'.PHP_EOL, $file);
        }

        $line = $throwable->getLine();
        if (!empty($line)) {
            $text .= sprintf('Line: %s'.PHP_EOL, $line);
        }

        $trace = $throwable->getTraceAsString();
        if (!empty($trace)) {
            $text .= sprintf('Trace: %s', $trace);
        }

        return $text;
    }

    /**
     * Write to the error log if displayErrorDetails is false
     *
     * @param  \Exception|\Throwable $throwable The caught Throwable object.
     * @return void
     */
    protected function writeToErrorLog($throwable)
    {
        if ($this->displayErrorDetails()) {
            return;
        }

        $prev = $this->translator()->translate('Previous error:', [], 'charcoal');
        $note = $this->translator()->translate(
            'View in rendered output by enabling the "displayErrorDetails" setting.',
            [],
            'charcoal'
        );

        $message = $this->renderThrowableAsText($throwable);
        while ($throwable = $throwable->getPrevious()) {
            $message .= PHP_EOL.$prev.PHP_EOL;
            $message .= $this->renderThrowableAsText($throwable);
        }

        $message .= PHP_EOL.$note.PHP_EOL;

        $this->logError($message);
    }

    /**
     * Wraps the error_log function so that this can be easily tested
     *
     * @param  string $message The message to log.
     * @return void
     */
    protected function logError($message)
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->error($message);
        } else {
            error_log($message);
        }
    }

    /**
     * Render Text Error
     *
     * @param  \Exception|\Throwable $throwable The caught Throwable object.
     * @return string
     */
    protected function renderTextMessage($throwable)
    {
        $message = $this->getSummary();
        if ($this->displayErrorDetails()) {
            $prev = $this->translator()->translate('Previous error:', [], 'charcoal');

            $message .= PHP_EOL.$this->renderThrowableAsText($throwable);
            while ($throwable = $throwable->getPrevious()) {
                $message .= PHP_EOL.$prev.PHP_EOL;
                $message .= $this->renderThrowableAsText($throwable);
            }
        }

        return $message;
    }

    /**
     * Render JSON Error
     *
     * @param  \Exception|\Throwable $throwable The caught Throwable object.
     * @return string
     */
    protected function renderJsonMessage($throwable)
    {
        $json  = [
            'message' => $this->getSummary(),
        ];

        if ($this->displayErrorDetails()) {
            $json['error'] = [];

            do {
                $json['error'][] = [
                    'type'    => get_class($throwable),
                    'code'    => $throwable->getCode(),
                    'message' => $throwable->getMessage(),
                    'file'    => $throwable->getFile(),
                    'line'    => $throwable->getLine(),
                    'trace'   => explode("\n", $throwable->getTraceAsString()),
                ];
            } while ($throwable = $throwable->getPrevious());
        }

        return json_encode($json, JSON_PRETTY_PRINT);
    }

    /**
     * Render XML Error
     *
     * @param  \Exception|\Throwable $throwable The caught Throwable object.
     * @return string
     */
    protected function renderXmlMessage($throwable)
    {
        $xml = "<error>\n  <message>".$this->getSummary()."</message>\n";
        if ($this->displayErrorDetails()) {
            do {
                $xml .= "  <exception>\n";
                $xml .= '    <type>'.get_class($throwable)."</type>\n";
                $xml .= '    <code>'.$throwable->getCode()."</code>\n";
                $xml .= '    <message>'.$this->createCdataSection($throwable->getMessage())."</message>\n";
                $xml .= '    <file>'.$throwable->getFile()."</file>\n";
                $xml .= '    <line>'.$throwable->getLine()."</line>\n";
                $xml .= '    <trace>'.$this->createCdataSection($throwable->getTraceAsString())."</trace>\n";
                $xml .= "  </exception>\n";
            } while ($throwable = $throwable->getPrevious());
        }
        $xml .= '</error>';

        return $xml;
    }

    /**
     * Returns a CDATA section with the given content.
     *
     * @param  string $content The Character-Data to mark.
     * @return string
     */
    protected function createCdataSection($content)
    {
        return sprintf('<![CDATA[%s]]>', str_replace(']]>', ']]]]><![CDATA[>', $content));
    }

    /**
     * Render error as HTML.
     *
     * @param  \Exception|\Throwable $throwable The caught Throwable object.
     * @return string
     */
    protected function renderHtmlError($throwable)
    {
        $code    = $throwable->getCode();
        $message = $throwable->getMessage();
        $file    = $throwable->getFile();
        $line    = $throwable->getLine();
        $trace   = $throwable->getTraceAsString();

        $html = sprintf('<div><strong>Type:</strong> %s</div>', get_class($throwable));

        if ($code) {
            $html .= sprintf('<div><strong>Code:</strong> %s</div>', $code);
        }

        if ($message) {
            $html .= sprintf('<div><strong>Message:</strong> %s</div>', htmlentities($message));
        }

        if ($file) {
            $html .= sprintf('<div><strong>File:</strong> %s</div>', $file);
        }

        if ($line) {
            $html .= sprintf('<div><strong>Line:</strong> %s</div>', $line);
        }

        if ($trace) {
            $html .= '<h2>'.$this->translator()->translate('Trace', [], 'charcoal').'</h2>';
            $html .= sprintf('<pre>%s</pre>', htmlentities($trace));
        }

        return $html;
    }

    /**
     * Prepare the template data for rendering.
     *
     * @param  array|\ArrayAccess $data Raw template data.
     * @return array|\ArrayAccess Expanded and processed template data.
     */
    protected function parseTemplateData($data = [])
    {
        $error = $this->getThrown();

        $html = $this->renderHtmlError($error);
        while ($error = $error->getPrevious()) {
            $html .= '<h2>'.$this->translator()->translate('Previous error').'</h2>';
            $html .= $this->renderHtmlError($error);
        }

        $data['htmlErrorDetails']    = $html;
        $data['displayErrorDetails'] = $this->displayErrorDetails() && !empty($html);

        return parent::parseTemplateData($data);
    }
}
