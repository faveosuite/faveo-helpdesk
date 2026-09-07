<?php

/*
 * This file is part of the Fetch package.
 *
 * (c) Robert Hafner <tedivm@tedivm.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fetch;

/**
 * This library is a wrapper around the Imap library functions included in php. This class in particular manages a
 * connection to the server (imap, pop, etc) and allows for the easy retrieval of stored messages.
 *
 * @package Fetch
 * @author  Robert Hafner <tedivm@tedivm.com>
 */
class Server
{
    /**
     * When SSL isn't compiled into PHP we need to make some adjustments to prevent soul crushing annoyances.
     *
     * @var bool
     */
    public static $sslEnable = true;

    /**
     * These are the flags that depend on ssl support being compiled into imap.
     *
     * @var array
     */
    public static $sslFlags = array('ssl', 'validate-cert', 'novalidate-cert', 'tls', 'notls');

    /**
     * This is used to prevent the class from putting up conflicting tags. Both directions- key to value, value to key-
     * are checked, so if "novalidate-cert" is passed then "validate-cert" is removed, and vice-versa.
     *
     * @var array
     */
    public static $exclusiveFlags = array('validate-cert' => 'novalidate-cert', 'tls' => 'notls');

    /**
     * This is the domain or server path the class is connecting to.
     *
     * @var string
     */
    protected $serverPath;

    /**
     * This is the name of the current mailbox the connection is using.
     *
     * @var string
     */
    protected $mailbox = '';

    /**
     * This is the username used to connect to the server.
     *
     * @var string
     */
    protected $username;

    /**
     * This is the password used to connect to the server.
     *
     * @var string
     */
    protected $password;

    /**
     * This is an array of flags that modify how the class connects to the server. Examples include "ssl" to enforce a
     * secure connection or "novalidate-cert" to allow for self-signed certificates.
     *
     * @link http://us.php.net/manual/en/function.imap-open.php
     * @var array
     */
    protected $flags = array();

    /**
     * This is the port used to connect to the server
     *
     * @var int
     */
    protected $port;

    /**
     * This is the set of options, represented by a bitmask, to be passed to the server during connection.
     *
     * @var int
     */
    protected $options = 0;

    /**
     * This is the set of connection parameters
     *
     * @var array
     */
    protected $params = array();

    /**
     * This is the resource connection to the server. It is required by a number of imap based functions to specify how
     * to connect.
     *
     * @var resource
     */
    protected $imapStream;

    /**
     * This is the name of the service currently being used. Imap is the default, although pop3 and nntp are also
     * options
     *
     * @var string
     */
    protected $service = 'imap';

    /**
     * This constructor takes the location and service thats trying to be connected to as its arguments.
     *
     * @param string      $serverPath
     * @param null|int    $port
     * @param null|string $service
     */
    public function __construct($serverPath, $port = 143, $service = 'imap')
    {
        $this->serverPath = $serverPath;

        $this->port = $port;

        switch ($port) {
            case 143:
                $this->setFlag('novalidate-cert');
                break;

            case 993:
                $this->setFlag('ssl');
                break;
        }

        $this->service = $service;
    }

    /**
     * This function sets the username and password used to connect to the server.
     *
     * @param string $username
     * @param string $password
     * @param bool   $tryFasterAuth tries to auth faster by disabling GSSAPI & NTLM auth methods (set to false if you use either of these auth methods)
     */
    public function setAuthentication($username, $password, $tryFasterAuth=true)
    {
        $this->username = $username;
        $this->password = $password;
        if ($tryFasterAuth) {
            $this->setParam('DISABLE_AUTHENTICATOR', array('GSSAPI','NTLM'));
        }
    }

    /**
     * This function sets the mailbox to connect to.
     *
     * @param  string $mailbox
     * @return bool
     */
    public function setMailBox($mailbox = '')
    {
        if (!$this->hasMailBox($mailbox)) {
            return false;
        }

        $this->mailbox = $mailbox;
        if (isset($this->imapStream)) {
            $this->setImapStream();
        }

        return true;
    }

    public function getMailBox()
    {
        return $this->mailbox;
    }

    /**
     * This function sets or removes flag specifying connection behavior. In many cases the flag is just a one word
     * deal, so the value attribute is not required. However, if the value parameter is passed false it will clear that
     * flag.
     *
     * @param string           $flag
     * @param null|string|bool $value
     */
    public function setFlag($flag, $value = null)
    {
        if (!self::$sslEnable && in_array($flag, self::$sslFlags))
            return;

        if (isset(self::$exclusiveFlags[$flag])) {
            $kill = self::$exclusiveFlags[$flag];
        } elseif ($index = array_search($flag, self::$exclusiveFlags)) {
            $kill = $index;
        }

        if (isset($kill) && false !== $index = array_search($kill, $this->flags))
            unset($this->flags[$index]);

        $index = array_search($flag, $this->flags);
        if (isset($value) && $value !== true) {
            if ($value == false && $index !== false) {
                unset($this->flags[$index]);
            } elseif ($value != false) {
                $match = preg_grep('/' . $flag . '/', $this->flags);
                if (reset($match)) {
                    $this->flags[key($match)] = $flag . '=' . $value;
                } else {
                    $this->flags[] = $flag . '=' . $value;
                }
            }
        } elseif ($index === false) {
            $this->flags[] = $flag;
        }
    }

    /**
     * This funtion is used to set various options for connecting to the server.
     *
     * @param  int        $bitmask
     * @throws \Exception
     */
    public function setOptions($bitmask = 0)
    {
        if (!is_numeric($bitmask))
            throw new \RuntimeException('Function requires numeric argument.');

        $this->options = $bitmask;
    }

    /**
     * This function is used to set connection parameters
     *
     * @param string $key
     * @param string $value
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * This function gets the current saved imap resource and returns it.
     *
     * @return resource
     */
    public function getImapStream()
    {
        if (empty($this->imapStream))
            $this->setImapStream();

        return $this->imapStream;
    }

    /**
     * This function takes in all of the connection date (server, port, service, flags, mailbox) and creates the string
     * thats passed to the imap_open function.
     *
     * @return string
     */
    public function getServerString()
    {
        $mailboxPath = $this->getServerSpecification();

        if (isset($this->mailbox))
            $mailboxPath .= $this->mailbox;

        return $mailboxPath;
    }

    /**
     * Returns the server specification, without adding any mailbox.
     *
     * @return string
     */
    protected function getServerSpecification()
    {
        $mailboxPath = '{' . $this->serverPath;

        if (isset($this->port))
            $mailboxPath .= ':' . $this->port;

        if ($this->service != 'imap')
            $mailboxPath .= '/' . $this->service;

        foreach ($this->flags as $flag) {
            $mailboxPath .= '/' . $flag;
        }

        $mailboxPath .= '}';

        return $mailboxPath;
    }

    /**
     * This function creates or reopens an imapStream when called.
     *
     */
    protected function setImapStream()
    {
        if (!empty($this->imapStream)) {
            if (!imap_reopen($this->imapStream, $this->getServerString(), $this->options, 1))
                throw new \RuntimeException(imap_last_error());
        } else {
            $imapStream = @imap_open($this->getServerString(), $this->username, $this->password, $this->options, 1, $this->params);

            if ($imapStream === false)
                throw new \RuntimeException(imap_last_error());

            $this->imapStream = $imapStream;
        }
    }

    /**
     * This returns the number of messages that the current mailbox contains.
     *
     * @param  string $mailbox
     * @return int
     */
    public function numMessages($mailbox='')
    {
        $cnt = 0;
        if ($mailbox==='') {
            $cnt = imap_num_msg($this->getImapStream());
        } elseif ($this->hasMailbox($mailbox) && $mailbox !== '') {
            $oldMailbox = $this->getMailBox();
            $this->setMailbox($mailbox);
            $cnt = $this->numMessages();
            $this->setMailbox($oldMailbox);
        }

        return ((int) $cnt);
    }

    /**
     * This function returns an array of ImapMessage object for emails that fit the criteria passed. The criteria string
     * should be formatted according to the imap search standard, which can be found on the php "imap_search" page or in
     * section 6.4.4 of RFC 2060
     *
     * @link http://us.php.net/imap_search
     * @link http://www.faqs.org/rfcs/rfc2060
     * @param  string   $criteria
     * @param  null|int $limit
     * @return array    An array of ImapMessage objects
     */
    public function search($criteria = 'ALL', $limit = null)
    {
        if ($results = imap_search($this->getImapStream(), $criteria, SE_UID)) {
            if (isset($limit) && count($results) > $limit)
                $results = array_slice($results, 0, $limit);

            $messages = array();

            foreach ($results as $messageId)
                $messages[] = new Message($messageId, $this);

            return $messages;
        } else {
            return array();
        }
    }

    /**
     * This function returns the recently received emails as an array of ImapMessage objects.
     *
     * @param  null|int $limit
     * @return array    An array of ImapMessage objects for emails that were recently received by the server.
     */
    public function getRecentMessages($limit = null)
    {
        return $this->search('Recent', $limit);
    }

    /**
     * Returns the emails in the current mailbox as an array of ImapMessage objects.
     *
     * @param  null|int  $limit
     * @return Message[]
     */
    public function getMessages($limit = null)
    {
        $numMessages = $this->numMessages();

        if (isset($limit) && is_numeric($limit) && $limit < $numMessages)
            $numMessages = $limit;

        if ($numMessages < 1)
            return array();

        $stream   = $this->getImapStream();
        $messages = array();
        for ($i = 1; $i <= $numMessages; $i++) {
            $uid        = imap_uid($stream, $i);
            $messages[] = new Message($uid, $this);
        }

        return $messages;
    }

    /**
     * Returns the emails in the current mailbox as an array of ImapMessage objects
     * ordered by some ordering
     *
     * @see    http://php.net/manual/en/function.imap-sort.php
     * @param  int       $orderBy
     * @param  bool      $reverse
     * @param  int       $limit
     * @return Message[]
     */
    public function getOrderedMessages($orderBy, $reverse, $limit)
    {
        $msgIds = imap_sort($this->getImapStream(), $orderBy, $reverse ? 1 : 0, SE_UID);

        return array_map(array($this, 'getMessageByUid'), array_slice($msgIds, 0, $limit));
    }

    /**
     * Returns the requested email or false if it is not found.
     *
     * @param  int          $uid
     * @return Message|bool
     */
    public function getMessageByUid($uid)
    {
        try {
            $message = new \Fetch\Message($uid, $this);

            return $message;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * This function removes all of the messages flagged for deletion from the mailbox.
     *
     * @return bool
     */
    public function expunge()
    {
        return imap_expunge($this->getImapStream());
    }

    /**
     * Checks if the given mailbox exists.
     *
     * @param $mailbox
     *
     * @return bool
     */
    public function hasMailBox($mailbox)
    {
        return (boolean) $this->getMailBoxDetails($mailbox);
    }

    /**
    * Return information about the mailbox or mailboxes
    *
    * @param $mailbox
    *
    * @return array
    */
    public function getMailBoxDetails($mailbox)
    {
        return imap_getmailboxes(
            $this->getImapStream(),
            $this->getServerString(),
            $this->getServerSpecification() . $mailbox
        );
    }

    /**
     * Creates the given mailbox.
     *
     * @param $mailbox
     *
     * @return bool
     */
    public function createMailBox($mailbox)
    {
        return imap_createmailbox($this->getImapStream(), $this->getServerSpecification() . $mailbox);
    }

    /**
     * List available mailboxes
     *
     * @param string $pattern
     *
     * @return array
     */
    public function listMailBoxes($pattern = '*')
    {
        return imap_list($this->getImapStream(), $this->getServerSpecification(), $pattern);
    }

    /**
     * Deletes the given mailbox.
     *
     * @param $mailbox
     *
     * @return bool
     */
     public function deleteMailBox($mailbox)
     {
         return imap_deletemailbox($this->getImapStream(), $this->getServerSpecification() . $mailbox);
     }
}
