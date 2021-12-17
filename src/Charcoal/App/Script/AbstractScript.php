<?php

namespace Charcoal\App\Script;

use InvalidArgumentException;
use RuntimeException;

// From PSR-3
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// From 'league/climate'
use League\CLImate\CLImate;
use League\CLImate\Util\Reader\ReaderInterface;

// From 'charcoal-config'
use Charcoal\Config\AbstractEntity;

// From 'charcoal-app'
use Charcoal\App\AppInterface;
use Charcoal\App\Script\ScriptInterface;

/**
 * Abstract CLI Script
 */
abstract class AbstractScript extends AbstractEntity implements
    LoggerAwareInterface,
    ScriptInterface
{
    use LoggerAwareTrait;

    /**
     * Default behaviour of the controller.
     */
    const DEFAULT_ARG_QUIET       = false;
    const DEFAULT_ARG_VERBOSE     = false;
    const DEFAULT_ARG_INTERACTIVE = true;
    const DEFAULT_ARG_DRYRUN      = false;

    /**
     * Command-line argument names available with every command.
     */
    const ARG_HELP           = 'help';
    const ARG_QUIET          = 'quiet';
    const ARG_VERBOSE        = 'verbose';
    const ARG_INTERACTIVE    = 'interactive';
    const ARG_NO_INTERACTION = 'no-interaction';
    const ARG_DRY_RUN        = 'dry-run';

    /**
     * @var string $ident
     */
    private $ident;

    /**
     * @var string $description
     */
    private $description;

    /**
     * @var array $arguments
     */
    private $arguments;

    /**
     * @var CLImate $climate
     */
    private $climate;

    /**
     * @var ReaderInterface $cliamteReader
     */
    private $climateReader;

    /**
     * @var boolean $quiet
     */
    private $quiet;

    /**
     * @var boolean $verbose
     */
    private $verbose;

    /**
     * @var boolean $interactive
     */
    private $interactive;

    /**
     * @var boolean $dryRun
     */
    private $dryRun;

    /**
     * Return a new CLI script.
     *
     * @param array|\ArrayAccess $data The dependencies (app and logger).
     */
    public function __construct($data = null)
    {
        $this->setLogger($data['logger']);
        $this->setClimate($data['climate']);

        if (isset($data['climate_reader'])) {
            $this->setClimateReader($data['climate_reader']);
        }

        if (isset($data['container'])) {
            $this->setDependencies($data['container']);
        }
    }

    /**
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    final public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        $this->init();

        $climate   = $this->climate();
        $arguments = $climate->arguments;

        if ($arguments->defined(self::ARG_HELP)) {
            $climate->usage();
            return $response;
        }

        if ($arguments->defined(self::ARG_QUIET) && $arguments->defined(self::ARG_VERBOSE)) {
            $climate->error(sprintf(
                'You must choose one of --%s or --%s',
                self::ARG_QUIET,
                self::ARG_VERBOSE
            ));
            return $response;
        }

        if ($arguments->defined(self::ARG_QUIET)) {
            $this->setQuiet(true);
        }

        if ($arguments->defined(self::ARG_VERBOSE)) {
            $this->setVerbose(true);
        }

        if (
            $arguments->defined(self::ARG_INTERACTIVE) &&
            $arguments->defined(self::ARG_NO_INTERACTION)
        ) {
            $climate->error(sprintf(
                'You must choose one of --%s or --%s',
                self::ARG_INTERACTIVE,
                self::ARG_NO_INTERACTION
            ));
            return $response;
        }

        if ($arguments->defined(self::ARG_INTERACTIVE)) {
            $this->setInteractive(true);
        }

        if ($arguments->defined(self::ARG_NO_INTERACTION)) {
            $this->setInteractive(false);
        }

        if ($arguments->defined(self::ARG_DRY_RUN)) {
            $this->setDryRun(true);
        }

        $arguments->parse();

        return $this->run($request, $response);
    }

    /**
     * Filter the default arguments.
     *
     * Filters:
     * 1. Removes --quiet if script is quiet by default.
     * 2. Removes --verbose if script is verbose by default.
     * 3. Removes either --interactive or --no-interaction depending on
     *   if script is interactive by default.
     *
     * @param  array $arguments A map of argument definitions.
     * @return array
     */
    public function filterDefaultArguments(array $arguments)
    {
        // [^1]
        if (static::DEFAULT_ARG_QUIET) {
            unset($arguments[self::ARG_QUIET]);
        }

        // [^2]
        if (static::DEFAULT_ARG_VERBOSE) {
            unset($arguments[self::ARG_VERBOSE]);
        }

        // [^3]
        if (static::DEFAULT_ARG_INTERACTIVE) {
            unset($arguments[self::ARG_INTERACTIVE]);
        } else {
            unset($arguments[self::ARG_NO_INTERACTION]);
        }

        return $arguments;
    }

    /**
     * Retrieve the script's default arguments.
     *
     * @return array<string, array>
     */
    public function defaultArguments()
    {
        return [
            self::ARG_HELP => [
                'prefix'       => 'h',
                'longPrefix'   => self::ARG_HELP,
                'noValue'      => true,
                'description'  => 'Display help information.',
            ],
            self::ARG_QUIET => [
                'prefix'       => 'q',
                'longPrefix'   => self::ARG_QUIET,
                'noValue'      => true,
                'description'  => 'Only print error and warning messages.',
            ],
            self::ARG_VERBOSE => [
                'prefix'        => 'v',
                'longPrefix'    => self::ARG_VERBOSE,
                'noValue'       => true,
                'description'   => 'Increase verbosity of messages.',
            ],
            self::ARG_INTERACTIVE => [
                'prefix'       => 'i',
                'longPrefix'   => self::ARG_INTERACTIVE,
                'noValue'      => true,
                'description'  => 'Ask any interactive question.',
            ],
            self::ARG_NO_INTERACTION => [
                'prefix'       => 'n',
                'longPrefix'   => self::ARG_NO_INTERACTION,
                'noValue'      => true,
                'description'  => 'Do not ask any interactive question.',
            ],
            self::ARG_DRY_RUN => [
                'longPrefix'   => self::ARG_DRY_RUN,
                'noValue'      => true,
                'description'  => 'This will simulate the script and show you what would happen.',
            ],
        ];
    }

    /**
     * @param string $ident The script identifier string.
     * @throws InvalidArgumentException If the ident argument is not a string.
     * @return ScriptInterface Chainable
     */
    public function setIdent($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Ident must be a string'
            );
        }
        $this->ident = $ident;
        return $this;
    }

    /**
     * @return string
     */
    public function ident()
    {
        return $this->ident;
    }

    /**
     * @param string $description The script description.
     * @throws InvalidArgumentException If the deescription parameter is not a string.
     * @return ScriptInterface Chainable
     */
    public function setDescription($description)
    {
        if (!is_string($description)) {
            throw new InvalidArgumentException(
                'Description must be a string'
            );
        }
        $this->description = $description;
        $this->climate()->description($description);
        return $this;
    }

    /**
     * @return string
     */
    public function description()
    {
        return $this->description;
    }

    /**
     * @param boolean $quiet The quiet flag.
     * @return ScriptInterface Chainable
     */
    public function setQuiet($quiet)
    {
        if ($quiet !== null) {
            $quiet = (bool)$quiet;
        }

        $this->quiet = $quiet;
        return $this;
    }

    /**
     * @return boolean
     */
    public function quiet()
    {
        if ($this->quiet === null) {
            return static::DEFAULT_ARG_QUIET;
        }

        return $this->quiet;
    }

    /**
     * @param boolean $verbose The verbose flag.
     * @return ScriptInterface Chainable
     */
    public function setVerbose($verbose)
    {
        if ($verbose !== null) {
            $verbose = (bool)$verbose;
        }

        $this->verbose = $verbose;
        return $this;
    }

    /**
     * @return boolean
     */
    public function verbose()
    {
        if ($this->verbose === null) {
            return static::DEFAULT_ARG_VERBOSE;
        }

        return $this->verbose;
    }

    /**
     * @param boolean $interactive The interactive flag.
     * @return ScriptInterface Chainable
     */
    public function setInteractive($interactive)
    {
        if ($interactive !== null) {
            $interactive = (bool)$interactive;
        }

        $this->interactive = $interactive;
        return $this;
    }

    /**
     * @return boolean
     */
    public function interactive()
    {
        if ($this->interactive === null) {
            return static::DEFAULT_ARG_INTERACTIVE;
        }

        return $this->interactive;
    }

    /**
     * @param boolean $simulate The dry-run flag.
     * @return ScriptInterface Chainable
     */
    public function setDryRun($simulate)
    {
        if ($simulate !== null) {
            $simulate = (bool)$simulate;
        }

        $this->dryRun = $simulate;
        return $this;
    }

    /**
     * @return boolean
     */
    public function dryRun()
    {
        if ($this->dryRun === null) {
            return static::DEFAULT_ARG_DRYRUN;
        }

        return $this->dryRun;
    }

    /**
     * @param  array $arguments A map of argument definitions.
     * @return ScriptInterface Chainable
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = [];
        $this->addArguments($arguments);

        return $this;
    }

    /**
     * @param  array $arguments A map of argument definitions.
     * @return ScriptInterface Chainable
     */
    public function addArguments(array $arguments)
    {
        foreach ($arguments as $argName => $argOptions) {
            $this->addArgument($argName, $argOptions);
        }

        return $this;
    }

    /**
     * @param  string $argName The argument name.
     * @param  array  $options The argument definition.
     * @throws InvalidArgumentException If the argument name is not a string.
     * @return ScriptInterface Chainable
     */
    public function addArgument($argName, array $argOptions = [])
    {
        if (!is_string($argName)) {
            throw new InvalidArgumentException(
                'Argument name must be a string.'
            );
        }

        $this->arguments[$argName] = $argOptions;
        $this->climate()->arguments->add($argName, $argOptions);

        return $this;
    }

    /**
     * @return array $arguments
     */
    public function arguments()
    {
        return $this->arguments;
    }

    /**
     * @param  string $argName The argument identifier to retrieve options from.
     * @return array|null The argument options, or null if it does not exist.
     */
    public function argument($argName)
    {
        if (!isset($this->arguments[$argName])) {
            return null;
        }
        return $this->arguments[$argName];
    }

    /**
     * Give an opportunity to children classes to inject dependencies from a Pimple Container.
     *
     * Does nothing by default, reimplement in children classes.
     *
     * The `$container` DI-container (from `Pimple`) should not be saved or passed around, only to be used to
     * inject dependencies (typically via setters).
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        // This method is a stub. Reimplement in children template classes.
    }

    /**
     * @return void
     */
    protected function init()
    {
        $arguments = $this->filterDefaultArguments($this->defaultArguments());
        $this->setArguments($arguments);
    }

    /**
     * Safe climate getter.
     * If the instance was not previously set, create it.
     *
     * > CLImate is "PHP's best friend for the terminal."
     * > "CLImate allows you to easily output colored text, special formats, and more."
     *
     * @return CLImate
     */
    protected function climate()
    {
        return $this->climate;
    }

    /**
     * @return ReaderInterface
     */
    protected function climateReader()
    {
        return $this->climateReader;
    }

    /**
     * Retrieves the value of an argument either
     * from the argument list (if defined) or
     * from user input (if interactive).
     *
     * @param  string $argName An argument identifier.
     * @return mixed Returns the argument or prompt value.
     */
    protected function argOrInput($argName)
    {
        $cli  = $this->climate();
        $args = $cli->arguments;

        if (!$args->defined($argName) && $this->interactive()) {
            return $this->input($argName);
        }

        return $args->get($argName);
    }

    /**
     * Request a value from the user for the given argument.
     *
     * @param  string $name An argument identifier.
     * @throws RuntimeException If a radio or checkbox prompt has no options.
     * @return mixed Returns the prompt value.
     */
    protected function input($name)
    {
        $cli = $this->climate();
        $arg = $this->argument($name);

        if ($arg) {
            if (!empty($arg['inputType'])) {
                $type = $arg['inputType'];
            } elseif (!empty($arg['noValue'])) {
                $type = 'confirm';
            } else {
                $type = 'input';
            }

            if (!empty($arg['prompt'])) {
                $prompt = $arg['prompt'];
            } elseif (!empty($arg['description'])) {
                $prompt = $arg['description'];
            } else {
                $prompt = $name;
            }

            if (!empty($arg['choices'])) {
                $arg['options'] = $arg['choices'];
                $arg['acceptValue'] = $arg['choices'];
            }

            $accept = true;
        } else {
            $type   = 'input';
            $prompt = $name;
            $accept = false;
        }

        if (!in_array($type, [ 'confirm', 'checkboxes', 'radio' ])) {
            if (isset($arg['defaultValue'])) {
                $default = $arg['defaultValue'];

                if (is_bool($default) || is_null($default)) {
                    $default = var_export($default, true);
                }

                if ($default && is_string($default) || is_numeric($default)) {
                    $pattern = '/[\(\[\<]'.preg_quote($default, '/').'[\)\]\>]/';
                    if (!preg_match($pattern, $prompt)) {
                        $prompt .= ' ('.$default.')';
                    }
                }
            }
        }

        $ask = 'prompt';
        switch ($type) {
            case 'checkboxes':
            case 'radio':
                if (!isset($arg['options'])) {
                    throw new RuntimeException(
                        sprintf('The [%s] argument has no options.', $name)
                    );
                }

                $accept = false;
                $input  = $cli->{$type}($prompt, $arg['options'], $this->climateReader);
                break;

            case 'confirm':
                if (isset($arg['defaultValue'])) {
                    $arg['defaultValue'] = ($arg['defaultValue'] ? 'y' : 'n');
                }

                $ask   = 'confirmed';
                $input = $cli->confirm($prompt, $this->climateReader);
                break;

            case 'password':
                $input = $cli->password($prompt, $this->climateReader);
                $input->multiLine();
                break;

            case 'multiline':
                $input = $cli->input($prompt, $this->climateReader);
                $input->multiLine();
                break;

            default:
                $input = $cli->input($prompt, $this->climateReader);
                break;
        }

        if ($accept) {
            if (isset($arg['acceptValue'])) {
                if (is_array($arg['acceptValue']) || is_callable($arg['acceptValue'])) {
                    $input->accept($arg['acceptValue']);
                }
            }
        }

        if (isset($arg['defaultValue'])) {
            $input->defaultTo($arg['defaultValue']);
        }

        return $input->{$ask}();
    }

    /**
     * @param CLImate $climate A climate instance.
     * @return void
     */
    private function setClimate(CLImate $climate)
    {
        $this->climate = $climate;
    }

    /**
     * @param ReaderInterface $climateReader A climate reader.
     * @return void
     */
    private function setClimateReader(ReaderInterface $climateReader)
    {
        $this->climateReader = $climateReader;
    }
}
