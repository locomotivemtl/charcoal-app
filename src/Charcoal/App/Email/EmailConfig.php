<?php

namespace Charcoal\App\Email;

// From `PHP`
use \InvalidArgumentException as InvalidArgumentException;

// From `charcoal-core`
use \Charcoal\Config\AbstractConfig as AbstractConfig;

/**
 * Email configuration.
 */
class EmailConfig extends AbstractConfig
{
    /**
     * @var boolean $smtp
     */
    private $smtp = false;

    /**
     * @var string $smtp_hostname
     */
    private $smtp_hostname;

    /**
     * @var integer $smtp_port
     */
    private $smtp_port;

    /**
     * @var string $smtp_security
     */
    private $smtp_security = '';

    /**
     * @var boolean $smtp_auth
     */
    private $smtp_auth;

    /**
     * @var string $smtp_username
     */
    private $smtp_username;

    /**
     * @var string $smtp_password
     */
    private $smtp_password;

    /**
     * @var string $default_from
     */
    private $default_from;

    /**
     * @var string $default_reply_to
     */
    private $default_reply_to;

    /**
     * @var boolean $default_track
     */
    private $default_track;

    /**
     * @var boolean $default_log
     */
    private $default_log;

    /**
     * @return array
     */
    public function default_data()
    {
        return [
            'smtp'              => false,

            'default_from'      => '',
            'default_reply_to'  => '',

            'default_track'     => false,
            'default_log'       => true
        ];
    }

    /**
     * @param boolean $smtp If the email should be sent using SMTP or not.
     * @throws InvalidArgumentException If the smtp parameter is not boolean.
     * @return EmailConfig Chainable
     */
    public function set_smtp($smtp)
    {
        $this->smtp = !!$smtp;
        return $this;
    }

    /**
     * @return boolean
     */
    public function smtp()
    {
        return $this->smtp;
    }

    /**
     * @param string $smtp_hostname The SMTP hostname.
     * @throws InvalidArgumentException If the SMTP hostname parameter is not a string.
     * @return EmailConfig Chainable
     */
    public function set_smtp_hostname($smtp_hostname)
    {
        if (!is_string($smtp_hostname)) {
            throw new InvalidArgumentException(
                'SMTP Host name must be a string.'
            );
        }
        $this->smtp_hostname = $smtp_hostname;
        return $this;
    }

    /**
     * @return string
     */
    public function smtp_hostname()
    {
        return $this->smtp_hostname;
    }

    /**
     * @param integer $smtp_port The SMTP port.
     * @throws InvalidArgumentException If the SMTP port parameter is not an integer.
     * @return EmailConfig Chainable
     */
    public function set_smtp_port($smtp_port)
    {
        if (!is_int($smtp_port)) {
            throw new InvalidArgumentException(
                'SMTP Port must be an integer.'
            );
        }
        $this->smtp_port = $smtp_port;
        return $this;
    }

    /**
     * @return integer
     */
    public function smtp_port()
    {
        return $this->smtp_port;
    }

    /**
     * @param boolean $smtp_auth The SMTP authentication flag (if auth is required).
     * @return EmailConfig Chainable
     */
    public function set_smtp_auth($smtp_auth)
    {
        $this->smtp_auth = !!$smtp_auth;
        return $this;
    }

    /**
     * @return boolean
     */
    public function smtp_auth()
    {
        return $this->smtp_auth;
    }

    /**
     * @param string $smtp_username The SMTP username, if using authentication.
     * @throws InvalidArgumentException If the SMTP username parameter is not a string.
     * @return EmailConfig Chainable
     */
    public function set_smtp_username($smtp_username)
    {
        if (!is_string($smtp_username)) {
            throw new InvalidArgumentException(
                'SMTP Username must be a string.'
            );
        }
        $this->smtp_username = $smtp_username;
        return $this;
    }

    /**
     * @return string
     */
    public function smtp_username()
    {
        return $this->smtp_username;
    }

    /**
     * @param string $smtp_password The SMTP password, if using authentication.
     * @throws InvalidArgumentException If the parameter is not a string.
     * @return EmailConfig Chainable
     */
    public function set_smtp_password($smtp_password)
    {
        if (!is_string($smtp_password)) {
            throw new InvalidArgumentException(
                'SMTP Password must be a string.'
            );
        }
        $this->smtp_password = $smtp_password;
        return $this;
    }

    /**
     * @return string
     */
    public function smtp_password()
    {
        return $this->smtp_password;
    }

    /**
     * @param string $smtp_security The SMTP security type (empty "tls" or "ssl").
     * @throws InvalidArgumentException If the parameter is not valid (empty, "tls" or "ssl").
     * @return EmailConfig Chainable
     */
    public function set_smtp_security($smtp_security)
    {
        $valid_security = ['', 'tls', 'ssl'];
        if (in_array($smtp_security, $valid_security)) {
            throw new InvalidArgumentException(
                'SMTP Security is not valid. Must be "", "tls" or "ssl".'
            );
        }
        $this->smtp_security = $smtp_security;
        return $this;
    }

    /**
     * @return string
     */
    public function smtp_security()
    {
        return $this->smtp_security;
    }

    /**
     * @param mixed $default_from The default "From" email address.
     * @throws InvalidArgumentException If the default from email address is invalid.
     * @return EmailConfig Chainable
     */
    public function set_default_from($default_from)
    {
        if (is_string($default_from)) {
            $this->default_from = $default_from;
        } elseif (is_array($default_from)) {
            $this->default_from = $this->email_from_array($default_from);
        } else {
            throw new InvalidArgumentException(
                'Default "from" email address must be an array or a string'
            );
        }
        return $this;
    }

    /**
     * @return string
     */
    public function default_from()
    {
        return $this->default_from;
    }

    /**
     * @param mixed $default_reply_to The default "reply-to" email address.
     * @throws InvalidArgumentException If the default reply-to email address is invalid.
     * @return EmailConfig Chainable
     */
    public function set_default_reply_to($default_reply_to)
    {
        if (is_string($default_reply_to)) {
            $this->default_reply_to = $default_reply_to;
        } elseif (is_array($default_reply_to)) {
            $this->default_reply_to = $this->email_from_array($default_reply_to);
        } else {
            throw new InvalidArgumentException(
                'Default "from" email address must be an array or a string'
            );
        }
        return $this;
    }

    /**
     * @return string
     */
    public function default_reply_to()
    {
        return $this->default_reply_to;
    }

    /**
     * @param boolean $default_log The default log flag.
     * @return EmailConfig Chainable
     */
    public function set_default_log($default_log)
    {
        $this->default_log = !!$default_log;
        return $this;
    }

    /**
     * @return boolean
     */
    public function default_log()
    {
        return $this->default_log;
    }

    /**
     * @param boolean $default_track The default track flag.
     * @return EmailConfig Chainable
     */
    public function set_default_track($default_track)
    {
        $this->default_track = !!$default_track;
        return $this;
    }

    /**
     * @return boolean
     */
    public function default_track()
    {
        return $this->default_track;
    }

    /**
     * @param array $email_array An email array (containing an "email" key and optionally a "name" key).
     * @throws InvalidArgumentException If the email parameter is not an array or invalid array.
     * @return string
     */
    protected function email_from_array(array $email_array)
    {
        if (!isset($email_array['email'])) {
            throw new InvalidArgumentException(
                'Email Array must atleast contain the email key.'
            );
        }

        $email = filter_var($email_array['email'], FILTER_SANITIZE_EMAIL);
        if (!isset($email_array['name'])) {
            return $email;
        }

        $name = str_replace('"', '', filter_var($email_array['name'], FILTER_SANITIZE_STRING));
        return '"'.$name.'" <'.$email.'>';
    }
}
