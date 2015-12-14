<?php

namespace Charcoal\App\Email;

// Dependencies from `PHP`
use \Exception;
use \InvalidArgumentException;

// From `phpmailer/phpmailer`
use \PHPMailer;

// Module `charcoal-config` dependencies
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Module `charcoal-view` dependencies
use \Charcoal\View\GenericView;
use \Charcoal\View\ViewableInterface;
use \Charcoal\View\ViewableTrait;

// Intra-module (`charcoal-app`) dependencies
use \Charcoal\App\AppAwareInterface;
use \Charcoal\App\AppAwareTrait;
use \Charcoal\App\LoggerAwareInterface;
use \Charcoal\App\LoggerAwareTrait;
use \Charcoal\App\Template\TemplateFactory;

// Local namespace dependencies
use \Charcoal\App\Email\EmailInterface;
use \Charcoal\App\Email\EmailConfig;
use \Charcoal\App\Email\EmailLog;

/**
 * Default implementation of the `EmailInterface`.
 *
 */
class Email implements
    AppAwareInterface,
    ConfigurableInterface,
    EmailInterface,
    LoggerAwareInterface,
    ViewableInterface
{
    use AppAwareTrait;
    use ConfigurableTrait;
    use LoggerAwareTrait;
    use ViewableTrait;

    /**
     * @var string $campaign
     */
    private $campaign;

    /**
     * @var array $to
     */
    private $to = [];

    /**
     * @var array $cc
     */
    private $cc = [];

    /**
     * @var array $bcc
     */
    private $bcc = [];

    /**
     * @var string $from
     */
    private $from;

    /**
     * @var string $reply_to
     */
    private $reply_to;

    /**
     * @var string $subject
     */
    private $subject;

     /**
      * @var string $msg_html
      */
    private $msg_html;

     /**
      * @var string $msg_txt
      */
    private $msg_txt;

    /**
     * @var array $attachments
     */
    private $attachments = [];

    /**
     * @var boolean $log
     */
    private $log;

    /**
     * @var boolean $track
     */
    private $track;

    /**
     * @var array $template_data
     */
    private $template_data = [];

    /**
     * @param array $data Dependencies.
     */
    public function __construct(array $data)
    {
        $this->set_app($data['app']);
        $this->set_logger($data['logger']);
    }

    /**
     * @param array $data The data to set.
     * @return Email Chainable
     */
    public function set_data(array $data)
    {
        foreach ($data as $prop => $val) {
            $func = [$this, 'set_'.$prop];

            if ($val === null) {
                continue;
            }

            if (is_callable($func)) {
                call_user_func($func, $val);
                unset($data[$prop]);
            } else {
                $this->{$prop} = $val;
            }
        }
        return $this;
    }

    /**
     * @param string $campaign The campaign identifier.
     * @throws InvalidArgumentException If the campaign parameter is invalid.
     * @return EmailInterface Chainable
     */
    public function set_campaign($campaign)
    {
        if (!is_string($campaign)) {
            throw new InvalidArgumentException(
                'Campaign must be a string'
            );
        }
        $this->campaign = $campaign;
        return $this;
    }

    /**
     * Get the campaign identifier.
     *
     * If it has not been explicitely set, it will be aut-generated (with uniqid).
     *
     * @return string
     */
    public function campaign()
    {
        if ($this->campaign === null) {
            $this->campaign = $this->generate_campaign();
        }
        return $this->campaign;
    }

    /**
     * @return string
     */
    protected function generate_campaign()
    {
        return uniqid();
    }

    /**
     * @param string|array $to The email's main recipient(s).
     * @throws InvalidArgumentException If parameter is invalid.
     * @return EmailInterface Chainable
     */
    public function set_to($to)
    {
        if (is_string($to)) {
            $to = [$to];
        }
        if (!is_array($to)) {
            throw new InvalidArgumentException(
                'To must be an array of recipients'
            );
        }
        $this->to = [];

        if (isset($to['email'])) {
            // Means we're not dealing with multiple emails
            $this->add_to($to);
        } else {
            foreach ($to as $t) {
                $this->add_to($t);
            }
        }
        return $this;
    }

    /**
     * @param mixed $to The email's recipient to add, either as a string or an "email" array.
     * @throws InvalidArgumentException If the to parameter is invalid.
     * @return EmailInterface Chainable
     */
    public function add_to($to)
    {
        if (!is_string($to) && !is_array($to)) {
            throw new InvalidArgumentException(
                'Email address must be an array or a string'
            );
        }

        // Assuming nobody's gonna set a from which is only a name
        if (is_string($to)) {
            // @todo Validation
            $to = [
                'email' => $to,
                'name' => ''
            ];
        }

        if (!isset($to['name'])) {
            $to['name'] = '';
        }

        $this->to[] = $to;

        return $this;
    }

    /**
     * @return string[] The email's recipients.
     */
    public function to()
    {
        return $this->to;
    }

    /**
     * @param string|array $cc The emails' carbon-copy (CC) recipient(s).
     * @throws InvalidArgumentException If the CC parameter is invalid.
     * @return EmailInterface Chainable
     */
    public function set_cc($cc)
    {
        if (is_string($cc)) {
            $cc = [$cc];
        }
        if (!is_array($cc)) {
            throw new InvalidArgumentException(
                'CC must be an array of recipients'
            );
        }
        $this->cc = [];

        if (isset($cc['email'])) {
            // Means we're not dealing with multiple emails
            $this->add_cc($cc);
        } else {
            foreach ($cc as $t) {
                $this->add_cc($t);
            }
        }

        return $this;
    }

    /**
     * @param mixed $cc The emails' carbon-copy (CC) recipient to add.
     * @throws InvalidArgumentException If the CC parameter is invalid.
     * @return EmailInterface Chainable
     */
    public function add_cc($cc)
    {
        if (!is_string($cc) && !is_array($cc)) {
            throw new InvalidArgumentException(
                'CC email address must be an array or a string'
            );
        }

        // Assuming nobody's gonna set a from which is only a name
        if (is_string($cc)) {
            // @todo Validation
            $cc = [
                'email' => $cc,
                'name' => ''
            ];
        }

        if (!isset($cc['name'])) {
            $cc['name'] = '';
        }

        $this->cc[] = $cc;

        return $this;
    }

    /**
     * @return string[] The emails' carbon-copy (CC) recipient(s).
     */
    public function cc()
    {
        return $this->cc;
    }

    /**
     * @param string|array $bcc The emails' black-carbon-copy (BCC) recipient(s).
     * @throws InvalidArgumentException If the BCC parameter is invalid.
     * @return EmailInterface Chainable
     */
    public function set_bcc($bcc)
    {
        if (is_string($bcc)) {
            // Means we have a straight email
            $bcc = [$bcc];
        }
        if (!is_array($bcc)) {
            throw new InvalidArgumentException(
                'BCC must be an array of recipients'
            );
        }
        $this->bcc = [];

        if (isset($bcc['email'])) {
            // Means we're not dealing with multiple emails
            $this->add_bcc($bcc);
        } else {
            foreach ($bcc as $t) {
                $this->add_bcc($t);
            }
        }

        return $this;
    }

    /**
     * @param mixed $bcc The emails' black-carbon-copy (BCC) recipient to add.
     * @throws InvalidArgumentException If the BCC parameter is invalid.
     * @return EmailInterface Chainable
     */
    public function add_bcc($bcc)
    {
        if (!is_string($bcc) && !is_array($bcc)) {
            throw new InvalidArgumentException(
                'BCC email address must be an array or a string'
            );
        }

        // Assuming nobody's gonna set a from which is only a name
        if (is_string($bcc)) {
            // @todo Validation
            $bcc = [
                'email' => $bcc,
                'name' => ''
            ];
        }

        if (!isset($bcc['name'])) {
            $bcc['name'] = '';
        }

        $this->bcc[] = $bcc;

        return $this;
    }

    /**
     * @return string[] The emails' black-carbon-copy (BCC) recipient(s).
     */
    public function bcc()
    {
        return $this->bcc;
    }

    /**
     * @param mixed $from The message's sender email address.
     * @throws InvalidArgumentException If the from parameter is invalid.
     * @return EmailInterface Chainable
     */
    public function set_from($from)
    {
        if (!is_string($from) && !is_array($from)) {
            throw new InvalidArgumentException(
                'From email address must be an array or a string'
            );
        }

        // Assuming nobody's gonna set a from which is only a name
        if (is_string($from)) {
            // @todo Validation
            $from = [
                'email' => $from,
                'name' => ''
            ];
        }

        if (!isset($from['name'])) {
            $from['name'] = '';
        }

        $this->from = $from;

        return $this;
    }

    /**
     * @return string The message's sender email address.
     */
    public function from()
    {
        if ($this->from === null) {
            $this->from = $this->config()->default_from();
        }
        return $this->from;
    }

    /**
     * Set the "reply-to" header field.
     *
     * @param mixed $reply_to The sender's reply-to email address.
     * @throws InvalidArgumentException If the reply_to parameter is invalid.
     * @return EmailInterface Chainable
     */
    public function set_reply_to($reply_to)
    {
        if (!is_string($reply_to) && !is_array($reply_to)) {
            throw new InvalidArgumentException(
                'Reply to email address must be an array or a string'
            );
        }

        if (is_string($reply_to)) {
            $reply_to = [
                'email' => $reply_to,
                'name' => ''
            ];
        }

        if (!isset($reply_to['name'])) {
            $reply_to['name'] = '';
        }

        $this->reply_to = $reply_to;

        return $this;
    }

    /**
     * @return string The sender's reply-to email address.
     */
    public function reply_to()
    {
        if ($this->reply_to === null) {
            $this->from = $this->config()->default_reply_to();
        }
        return $this->reply_to;
    }

    /**
     * @param string $subject The emails' subject.
     * @throws InvalidArgumentException If the subject parameter is invalid.
     * @return EmailInterface Chainable
     */
    public function set_subject($subject)
    {
        if (!is_string($subject)) {
            throw new InvalidArgumentException(
                'Subject needs to be a string'
            );
        }
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string The emails' subject.
     */
    public function subject()
    {
        return $this->subject;
    }

    /**
     * Explicitely set the HTML message body.
     *
     * If the HTML message is not explitely set here, it will be auto-generated.
     *
     * @param string $msg_html The HTML body string.
     * @throws InvalidArgumentException If the parameter is invalid.
     * @return EmailInterface Chainable
     */
    public function set_msg_html($msg_html)
    {
        if (!is_string($msg_html)) {
            throw new InvalidArgumentException(
                'HTML message must be a string'
            );
        }
        $this->msg_html = $msg_html;
        return $this;
    }

    /**
     * Get the email's HTML message body.
     *
     * If it has not been explitely set, it will be aut-generated from a template view.
     *
     * @return string The HTML body string.
     */
    public function msg_html()
    {
        if ($this->msg_html === null) {
            $this->msg_html = $this->generate_msg_html();
        }
        return $this->msg_html;
    }

    /**
     * Get the message's HTML content from the template, if applicable.
     *
     * @see ViewableInterface::render_template
     * @return string
     */
    protected function generate_msg_html()
    {
        $template_ident = $this->template_ident();
        if (!$template_ident) {
            $msg_html = '';
        } else {
            $msg_html = $this->render_template($template_ident);
        }
        return $msg_html;
    }

    /**
     * Explicitely set the message's text body.
     *
     * If the text message is not explicitely set here, it will be auto-generated from the HTML.
     *
     * @param string $msg_txt The message's text body.
     * @throws InvalidArgumentException If the parameter is invalid.
     * @return EmailInterface Chainable
     */
    public function set_msg_txt($msg_txt)
    {
        if (!is_string($msg_txt)) {
            throw new InvalidArgumentException('Text msg must be a string');
        }
        $this->msg_txt = $msg_txt;
        return $this;
    }

    /**
     * @return string
     */
    public function msg_txt()
    {
        if ($this->msg_txt === null) {
            $this->msg_txt = $this->generate_msg_txt();
        }
        return $this->msg_txt;
    }

    /**
     * @return string
     */
    protected function generate_msg_txt()
    {
        $msg_html = $this->msg_html();
        return $msg_html;
    }

    /**
     * @param array $attachments The file attachments.
     * @return EmailInterface Chainable
     */
    public function set_attachments(array $attachments)
    {
        foreach ($attachments as $att) {
            $this->add_attachment($att);
        }
        return $this;
    }

    /**
     * @param mixed $attachment The attachments.
     * @return EmailInterface Chainable
     */
    public function add_attachment($attachment)
    {
        $this->attachments[] = $attachment;
        return $this;
    }

    /**
     * @return array
     */
    public function attachments()
    {
        return $this->attachments;
    }

    /**
     * Enable or disable logging for this particular email.
     *
     * @param boolean $log The log flag.
     * @return EmailInterface Chainable
     */
    public function set_log($log)
    {
        $this->log = !!$log;
        return $this;
    }

    /**
     * @return boolean
     */
    public function log()
    {
        if ($this->log === null) {
            $this->log = $this->config()->default_log();
        }
        return $this->log;
    }

    /**
     * Enable or disable tracking for this particular email.
     *
     * @param boolean $track The track flag.
     * @return EmailInterface Chainable
     */
    public function set_track($track)
    {
        $this->track = !!$track;
        return $this;
    }

    /**
     * @return boolean
     */
    public function track()
    {
        if ($this->track === null) {
            $this->track = $this->config()->default_track();
        }
        return $this->track;
    }

    /**
     * Send the email to all recipients
     *
     * @return bool Success / Failure.
     */
    public function send()
    {
        $this->logger()->debug(
            'Attempting to send an email',
            $this->to()
        );

        $mail = new PHPMailer(true);

        try {
            $this->set_smtp_options($mail);

            $mail->CharSet = 'UTF-8';

            // Setting FROM
            $from = $this->from();

            // From DOC, $name = ''
            // Set from defines the default vars
            $mail->setFrom($from['email'], $from['name']);

            $to = $this->to();

            foreach ($to as $recipient) {
                // Default name set in set_to
                $mail->addAddress($recipient['email'], $recipient['name']);
            }

            $reply_to = $this->reply_to();
            if ($reply_to) {
                // Default name set in set_reply_to
                $mail->addReplyTo($reply_to['email'], $reply_to['name']);
            }
            $cc = $this->bcc();
            foreach ($cc as $cc_recipient) {
                // Default name set in add_cc
                $mail->addCC($cc_recipient['email'], $cc_recipient['name']);
            }
            $bcc = $this->bcc();
            foreach ($bcc as $bcc_recipient) {
                // Default name set in add_bcc
                $mail->addBCC($bcc_recipient['email'], $bcc_recipient['name']);
            }

            $attachments = $this->attachments();
            foreach ($attachments as $att) {
                $mail->addAttachment($att);
            }

            $mail->isHTML(true);

            $mail->Subject = $this->subject();
            $mail->Body    = $this->msg_html();
            $mail->AltBody = $this->msg_txt();

            $ret = $mail->send();

            $this->log_send($ret, $mail);

            return $ret;
        } catch (Exception $e) {
            $this->logger()->error('Error sending email: '.$e->getMessage());
        }
    }

    /**
     * @param PHPMailer $mail The PHPMailer to setup.
     * @return void
     */
    public function set_smtp_options(PHPMailer $mail)
    {
        $config = $this->config();
        if (!$config['smtp']) {
            return;
        }

        $this->logger()->debug(
            sprintf('Using SMTP %s server to send email', $config['smtp_hostname'])
        );

        $mail->IsSMTP();
        $mail->Host       = $config['smtp_hostname'];
        $mail->Port       = $config['smtp_port'];
        $mail->SMTPAuth   = $config['smtp_auth'];
        $mail->Username   = $config['smtp_username'];
        $mail->Password   = $config['smtp_password'];
        $mail->SMTPSecure = $config['smtp_security'];
    }

    /**
     * @return boolean Success / Failure.
     */
    public function queue()
    {
        return false;
    }

    /**
     * @param boolean $result Success or failure.
     * @param mixed   $mailer The raw mailer.
     * @return void
     */
    protected function log_send($result, $mailer)
    {
        if (!$result) {
            $this->logger()->error('Email could not be sent.');
        } else {
            $this->logger()->debug(sprintf('Email "%s" sent successfully.', $this->subject()), $this->to());
        }

        $recipients = array_merge(
            $this->to(),
            $this->cc(),
            $this->bcc()
        );
        foreach ($recipients as $to) {
            $log = new EmailLog([
                'logger'=>$this->logger()
            ]);

            $log->set_type('email');
            $log->set_action('send');

            $log->set_raw_response($mailer);

            $log->set_message_id($mailer->getLastMessageId());
            $log->set_campaign($this->campaign());

            $log->set_send_ts('now');

            $log->set_from($mailer->From);
            $log->set_to($to['email']);
            $log->set_subject($this->subject());

            $log->save();
        }

    }

    /**
     * @return void
     */
    protected function log_queue()
    {


    }
    /**
     * @param array $email_array An email array (containing an "email" key and optionally a "name" key).
     * @throws InvalidArgumentException If parameter is not an array or invalid array.
     * @return string
     */
    protected function email_from_array(array $email_array)
    {

        if (!isset($email_array['email'])) {
            throw new InvalidArgumentException(
                'Email array must atleast contain the email key.'
            );
        }

        $email = filter_var($email_array['email'], FILTER_SANITIZE_EMAIL);
        if (!isset($email_array['name'])) {
            return $email;
        }

        $name = str_replace('"', '', filter_var($email_array['name'], FILTER_SANITIZE_STRING));
        return '"'.$name.'" <'.$email.'>';
    }

    /**
     * ConfigurableInterface > create_config()
     *
     * @param array $data Optional config data.
     * @return EmailConfig
     */
    public function create_config(array $data = null)
    {
        $config = new EmailConfig();
        if ($data !== null) {
            $config->set_data($data);
        } else {
            // Use default app config
            $config->set_data($this->app()->config()->get('email'));
        }
        return $config;
    }

    /**
     * ViewableInterface > create_view()
     *
     * @param array $data Optional view data.
     * @return EmailView
     */
    public function create_view(array $data = null)
    {
        $view = new GenericView([
            'logger' => $this->logger
        ]);
        if ($data !== null) {
            $view->set_data($data);
        }
        return $view;
    }

    /**
     * @param array $data The template data.
     * @return Email Chainable
     */
    public function set_template_data(array $data)
    {
        $this->template_data = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function template_data()
    {
        return $this->template_data;
    }

    /**
     * Custom view controller for email.
     *
     * Unlike typical `Viewable` objects, the view controller is not the email itself
     * but an external "email" template.
     *
     * @see ViewableInterface::view_controller()
     * @return TemplateInterface|array
     */
    public function view_controller()
    {
        $template_ident = $this->template_ident();

        if (!$template_ident) {
            return [];
        }

        $template_factory = new TemplateFactory();
        $template = $template_factory->create($template_ident, [
            'app'    => $this->app,
            'logger' => $this->logger
        ]);

        $template->set_data($this->template_data());

        return $template;
    }
}
