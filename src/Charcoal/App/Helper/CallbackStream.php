<?php

namespace Charcoal\App\Helper;

use Psr\Http\Message\StreamInterface;

/**
 * Callback-based stream implementation.
 * Wraps a callback, and invokes it in order to stream it.
 * Only one invocation is allowed; multiple invocations will return an empty
 * string for the second and subsequent calls.
 */
class CallbackStream implements StreamInterface
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * Whether or not the callback has been previously invoked.
     * @var boolean
     */
    private $called = false;

    /**
     * CallbackStream constructor.
     * @param callable $callback The callback stream.
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->output();
    }

    /**
     * Execute the callback with output buffering.
     *
     * @return ?string
     */
    public function output()
    {
        if ($this->called) {
            return null;
        }

        $this->called = true;

        return call_user_func($this->callback);
    }

    /**
     * @return void
     */
    public function close()
    {
    }

    /**
     * @return null|callable
     */
    public function detach()
    {
        $callback       = $this->callback;
        $this->callback = null;

        return $callback;
    }

    /**
     * @return integer|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize()
    {
        return null;
    }

    /**
     * @return integer|boolean Position of the file pointer or false on error.
     */
    public function tell()
    {
        return 0;
    }

    /**
     * @return boolean
     */
    public function eof()
    {
        return $this->called;
    }

    /**
     * @return boolean
     */
    public function isSeekable()
    {
        return false;
    }

    /**
     * @link http://www.php.net/manual/en/function.fseek.php 1
     * @param integer $offset Stream offset.
     * @param integer $whence Specifies how the cursor position will be calculated
     *                    based on the seek offset. Valid values are identical to the built-in
     *                    PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *                    offset bytes SEEK_CUR: Set position to current location plus offset
     *                    SEEK_END: Set position to end-of-stream plus offset.
     * @return boolean Returns TRUE on success or FALSE on failure.
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        return false;
    }

    /**
     * @see  seek()
     * @link http://www.php.net/manual/en/function.fseek.php 1
     * @return boolean Returns TRUE on success or FALSE on failure.
     */
    public function rewind()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isWritable()
    {
        return false;
    }

    /**
     * @param string $string The string that is to be written.
     * @return integer|boolean Returns the number of bytes written to the stream on
     *                       success or FALSE on failure.
     */
    public function write($string)
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isReadable()
    {
        return true;
    }

    /**
     * @param integer $length Read up to $length bytes from the object and return
     *                    them. Fewer than $length bytes may be returned if underlying stream
     *                    call returns fewer bytes.
     * @return string|false Returns the data read from the stream, false if
     *                    unable to read or if an error occurs.
     */
    public function read($length)
    {
        unset($length);
        return $this->output();
    }

    /**
     * @return string
     */
    public function getContents()
    {
        return $this->output();
    }

    /**
     * @link http://php.net/manual/en/function.stream-get-meta-data.php 2
     * @param string $key Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is
     *                    provided. Returns a specific key value if a key is provided and the
     *                    value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
        if ($key === null) {
            return [];
        }

        return null;
    }
}
