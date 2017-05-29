<?php

namespace Charcoal\App\Script;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From PSR-11
use Psr\Container\ContainerInterface;

/**
 * Script are actions called from the CLI.
 *
 * Typically, with the `charcoal` bin.
 */
interface ScriptInterface
{

    /**
     * Set dependencies from the service locator.
     *
     * @param  ContainerInterface $container A service locator.
     * @return void
     */
    public function setDependencies(ContainerInterface $container);

    /**
     * @param string $ident The script identifier string.
     * @return ScriptInterface Chainable
     */
    public function setIdent($ident);

    /**
     * @return string
     */
    public function ident();

    /**
     * @param string $description The script description.
     * @return ScriptInterface Chainable
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function description();

    /**
     * @param array $arguments The script arguments array, as [key=>value].
     * @return ScriptInterface Chainable
     */
    public function setArguments(array $arguments);

    /**
     * @param string $argumentIdent The argument identifier.
     * @param array  $argument      The argument options.
     * @return ScriptInterface Chainable
     */
    public function addArgument($argumentIdent, array $argument);

    /**
     * @return array $arguments
     */
    public function arguments();

    /**
     * @param string $argumentIdent The argument identifier to retrieve options from.
     * @return array
     */
    public function argument($argumentIdent);

    /**
     * Run the script.
     *
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response);
}
