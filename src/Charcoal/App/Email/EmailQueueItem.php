<?php

namespace Charcoal\App\Email;

use \Exception;
use \InvalidArgumentException;

// Module `charcoal-core` dependencies
use \Charcoal\Model\AbstractModel;
use \Charcoal\Core\IndexableInterface;
use \Charcoal\Core\IndexableTrait;

// Module `charcoal-queue` dependencies
use \Charcoal\Queue\QueueItemInterface;
use \Charcoal\Queue\QueueItemTrait;

// Module `charcoal-app` dependencies
use \Charcoal\App\App;
use \Charcoal\App\Email\Email;

/**
 * Email queue item.
 */
class EmailQueueItem extends AbstractModel implements
    QueueItemInterface,
    IndexableInterface
{
    use QueueItemTrait;
    use IndexableTrait;

    /**
     * @var string $provider_ident
     */
    private $provider_ident;
    /**
     * @var string $ident
     */
    private $ident;

    /**
     * @var string $to
     */
    private $to;

    /**
     * @var string $from
     */
    private $from;

    /**
     * @var string $carrier
     */
    private $subject;

    /**
     * @var string $message
     */
    private $msg_html;

    /**
     * @var string $message
     */
    private $msg_txt;

    /**
     * @var string $campaign
     */
    private $campaign;

    /**
     * @return LoggerInterface
     */
    public function logger()
    {
        $app = App::instance();
        $container = $app->getContainer();
        return $container['logger'];
    }

    /**
     * @return string
     */
    public function key()
    {
        return 'id';
    }

    /**
     * @param string|null $ident The current ident.
     * @throws InvalidArgumentException If the ident is not a string.
     * @return AbstractMessage Chainable
     */
    public function set_ident($ident)
    {
        if ($ident === null) {
            $this->ident = null;
            return $this;
        }
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Ident needs to be a string'
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
     * @param string $email The email sender.
     * @throws InvalidArgumentException If the sender email is not a string.
     * @return SmsQueueItem Chainable
     */
    public function set_to($email)
    {
        if (!is_string($email)) {
            throw new InvalidArgumentException(
                'Queue item recipient: To needs to be a string'
            );
        }
        $this->to = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function to()
    {
        return $this->to;
    }

    /**
     * @param string $email The email recipient.
     * @throws InvalidArgumentException If the recipient email is not a string.
     * @return SmsQueueItem Chainable
     */
    public function set_from($email)
    {
        if (!is_string($email)) {
            throw new InvalidArgumentException(
                'To needs to be a string'
            );
        }
        $this->from = $email;
        return $this;
    }

    /**
     * Get the recipient's phone number.
     * @return string
     */
    public function from()
    {
        return $this->from;
    }

    /**
     * @param string $subject The email subject.
     * @throws InvalidArgumentException If the subject is not a string.
     * @return SmsQueueItem Chainable
     */
    public function set_subject($subject)
    {
        if (!is_string($subject)) {
            throw new InvalidArgumentException(
                'Message needs to be a string'
            );
        }
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     */
    public function subject()
    {
        return $this->subject;
    }

    /**
     * @param string $msg_html The message HTML.
     * @throws InvalidArgumentException If the html is not a string.
     * @return SmsQueueItem Chainable
     */
    public function set_msg_html($msg_html)
    {
        if (!is_string($msg_html)) {
            throw new InvalidArgumentException(
                'Message needs to be a string'
            );
        }
        $this->msg_html = $msg_html;
        return $this;
    }

    /**
     * @return string
     */
    public function msg_html()
    {
        return $this->msg_html;
    }

    /**
     * @param string $msg_txt The mesage text.
     * @throws InvalidArgumentException If the text is not a string.
     * @return SmsQueueItem Chainable
     */
    public function set_msg_txt($msg_txt)
    {
        if (!is_string($msg_txt)) {
            throw new InvalidArgumentException(
                'Message needs to be a string'
            );
        }
        $this->msg_txt = $msg_txt;
        return $this;
    }

    /**
     * @return string
     */
    public function msg_txt()
    {
        return $this->msg_txt;
    }

    /**
     * @param string $campaign The campaign identifier.
     * @throws InvalidArgumentException If the campaign is not a string.
     * @return SmsQueueItem Chainable
     */
    public function set_campaign($campaign)
    {
        if (!is_string($campaign)) {
            throw new InvalidArgumentException(
                'Message needs to be a string'
            );
        }
        $this->campaign = $campaign;
        return $this;
    }

    /**
     * @return string
     */
    public function campaign()
    {
        return $this->campaign;
    }

    /**
     * @param callable $cb         Callback function, will be called after completion (any results).
     * @param callable $success_cb Success callback, will be called only if sending the message succeeded.
     * @param callback $failure_cb Failure callback, in case of any errors / failures.
     * @return boolean
     */
    public function process(callable $cb = null, callable $success_cb = null, callable $failure_cb = null)
    {
        if ($this->processed() === true) {
            // Do not process twice, ever.
            return null;
        }
        $email = new Email([
            'app' => App::instance(),
            'logger' => $this->logger()
        ]);
        $email->set_data($this->data());
        try {
            $res = $email->send();
            if ($res === true) {
                $this->set_processed(true);
                $this->set_processed_date('now');
                $this->update();
                if ($success_cb !== null) {
                    $success_cb($this);
                }
            } else {
                if ($failure_cb !== null) {
                    $failure_cb($this);
                }
            }
            if ($cb !== null) {
                $cb($this);
            }
            return $res;

        } catch (Exception $e) {
            // Todo log error
            if ($failure_cb !== null) {
                $failure_cb($this);
            }
            return false;
        }
    }

    /**
     * @return boolean
     */
    public function pre_save()
    {
        parent::pre_save();
        $this->pre_save_queue_item();
        return true;
    }
}
