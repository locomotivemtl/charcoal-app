<?php

namespace Charcoal\Tests\App;

use Psr\Http\Message\ResponseInterface;

use GuzzleHttp\Client as HttpClient;

/**
 * Start a PHP builtin server instance, ready to serve a copy of the project.
 */
trait ServerTestTrait
{
    /**
     * @var mixed The process identifier of the built-in PHP server.
     */
    static private $serverProcess    = null;

    /**
     * @var string The hostname for the built-in PHP server.
     */
    static protected $serverHost = 'localhost';

    /**
     * @var string The port number on which the built-in PHP server will be opened.
     */
    static protected $serverPort = '8484';

    /**
     * @var null|string The server root directory, where it should be ran from.
     */
    static protected $serverRoot = null;

    /**
     * @var string The APPLICATION_ENV environment variable.
     */
    static protected $serverApplicationEnv = 'phpunit';

    /**
     * Retrieve the built-in PHP server URL.
     * @return string
     */
    protected static function serverURL()
    {
        return static::$serverHost.':'.static::$serverPort;
    }

    /**
     * Retrieve the root directory, where to start the built-in PHP server.
     * @return string
     */
    protected static function serverRoot()
    {
        if (static::$serverRoot !== null) {
            return static::$serverRoot;
        }
        return dirname(__DIR__).DIRECTORY_SEPARATOR.'www';
    }

    /**
     * Retrieve wether the tests are run on windows or not.
     * @return bool
     */
    protected static function isWindows()
    {
        return (stristr(php_uname('s'), 'win') !== false);
    }

    /**
     * Start a built-in PHP server process.
     * @beforeClass
     */
    public static function bootUpBuiltInServer()
    {
        $command = sprintf('php -S %s -t %s',
            static::serverURL(),
            static::serverRoot()
        );

        if(static::isWindows()) {
            $command = sprintf(
                'set APPLICATION_ENV=%s; start /b %s',
                static::$serverApplicationEnv,
                $command
            );
        } else {
            $command = sprintf(
                'APPLICATION_ENV=%s %s',
                    static::$serverApplicationEnv,
                    $command
            );
        }
        static::$serverProcess = popen($command, 'r');

        sleep(2);
    }

    /**
     * Terminates the built-in PHP server process.
     * @afterClass
     */
    public static function turnDownBuiltInServer()
    {
        pclose(static::$serverProcess);
    }

    /**
     * @param array $request The request data (method, route, options).
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    protected function callRequest(array $request)
    {
        $route = str_replace('.', '', $request['route']);
        $client = new HttpClient();
        $res = $client->request(
            $request['method'],
            'http://'.static::serverURL().$route,
            $request['options']
        );
        return $res;
    }

    /**
     * @param ResponseInterface $response
     * @param array $expected
     */
    protected function assertResponseMatchesExpected(ResponseInterface $response, array $expected)
    {
        if (isset($expected['statusCode']) && $expected['statusCode']) {
            $this->assertEquals($expected['statusCode'], $response->getStatusCode());
        }
        if (isset($expected['json']) && $expected['json']) {
            $results = json_decode((string)$response->getBody(), true);
            foreach($expected['json'] as $k=>$v) {
                if (is_array($v)) {
                    $this->assertArraySubset($v, $results[$k]);
                } else {
                    $this->assertEquals($v, $results[$k]);
                }
            }
        }
        if (isset($expected['body']) && $expected['body']) {
            $body = (string)$response->getBody();
            foreach($expected['body'] as $regexp) {
                $this->assertRegExp($regexp, $body);
            }
        }
    }

}