<?php

namespace TgEmail;

use TgLog\Log;
use TgUtils\Date;
use TgUtils\Request;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Central mail handler using PHPMailer.
 *
 * @author ralph
 *        
 */
class EmailQueue {

    /** Constant for blocking any mail sending */
    public const BLOCK = 'block';
    /** Constant for rerouting mails to admin users */
    public const REROUTE = 'reroute';
    /** Constant for adding admin user to BCC */
    public const BCC = 'bcc';
    /** Constant for default mail sending */
    public const DEFAULT = 'default';
    
    protected $mailer;

    protected $config;

    protected $mailDAO;
    
    public function __construct($config, $mailDAO = NULL) {
        $this->config  = $config;
        $this->mailDAO = $mailDAO;
        $this->mailer  = NULL;
    }

    public function createTestMail() {
        $rc = new Email();
        $rc->setSender($this->config->getDefaultSender());
        $rc->setBody(Email::TEXT, 'This is a successfull e-mail test (TXT)');
        $rc->setBody(Email::HTML, '<html><body><h1>Success</h1><p>This is a successfull e-mail test (HTML)</p></body></html>');
        $rc->addTo($this->config->getDebugAddress());
        $rc->setSubject($this->config->getSubjectPrefix() . 'Test-Mail');
        return $rc;
    }
    
    /**
     * Sends a test-mail to private account
     */
    public function sendTestMail() {
        $email = $this->createTestMail();
        return $this->_send($email);
    }
    
    /**
     * Set a new mail mode.
     * @param string $mailMode - the new mail mode
     * @param object $config   - the configuration of this mail mode (optional when config already available or not required)
     */
    public function setMailMode($mailMode, $config = NULL) {
        $this->config->setMailMode($mailMode, $config);
    }
    
    protected function getMailer() {
        if ($this->mailer == null) {
            $this->mailer = new PHPMailer();
            $this->mailer->IsSMTP(); // telling the class to use SMTP
            $this->mailer->SMTPDebug  = $this->config->getSmtpConfig()->getDebugLevel();
            $this->mailer->SMTPAuth   = $this->config->getSmtpConfig()->isAuth();
            $this->mailer->SMTPSecure = $this->config->getSmtpConfig()->getSecureOption();
            $this->mailer->Port       = $this->config->getSmtpConfig()->getPort();
            $this->mailer->Host       = $this->config->getSmtpConfig()->getHost();
            $this->mailer->Username   = $this->config->getSmtpConfig()->getUsername();
            $this->mailer->Password   = $this->config->getSmtpConfig()->getPassword();
            $this->mailer->CharSet    = $this->config->getSmtpConfig()->getCharset();
            $this->mailer->Encoding   = 'base64';
        } else {
            $this->mailer->clearAllRecipients();
            $this->mailer->clearAttachments();
            $this->mailer->clearCustomHeaders();
            $this->mailer->clearReplyTos();
        }
        return $this->mailer;
    }

    /**
     * Synchronously send emails from queue according to priority.
     */
    public function processQueue($maxTime = 0) {
        if ($maxTime <= 0) $maxTime = 60;
        
        if ($this->mailDAO != NULL) {
            // Make sure the request object was created
            Request::getRequest();
            
            // Return statistics
            $rc = new \stdClass();
            $rc->pending   = 0;
            $rc->skipped   = 0;
            $rc->processed = 0;
            $rc->sent      = 0;
            $rc->failed    = 0;
        
            // do housekeeping
            $this->mailDAO->housekeeping();
            
            // Retrieve pending emails
            $emails = $this->mailDAO->getPendingEmails();
            $rc->pending = count($emails);
            foreach ($emails as $email) {
                // send
                if ($this->sendByUid($email->uid, TRUE)) {
                    $rc->sent++;
                } else {
                    $rc->failed++;
                }
                $rc->processed++;
                if (Request::getRequest()->getElapsedTime() > $maxTime) break;
            }
            return $rc;
        }
        throw new EmailException('QueueProcessing not supported. No DAO available.');
    }

    /**
     * Synchronously send email from queue with id.
     */
    public function sendByUid($uid, $checkStatus = FALSE) {
        if ($this->mailDAO != NULL) {
            // Retrieve
            $email = $this->mailDAO->get($uid);
            
            if ($email != NULL) {
                // Mark as being processed
                $email->status = Email::PROCESSING;
                $this->mailDAO->save($email);

                // send
                $rc = $this->_send($email);
                
                // Save
                $email->status = Email::SENT;
                if (!$rc) {
                    $email->failed_attempts ++;
                    if ($email->failed_attempts >= 3) {
                        $email->status = Email::FAILED;
                        foreach ($email->getAttachments() AS $a) {
                            if ($a->deleteAfterSent && $a->deleteAfterFailed) {
                                unlink($a->path);
                            }
                        }
                    } else {
                        $email->status = Email::PENDING;
                    }
                } else {
                    $email->sent_time = new Date(time(), $this->config->getTimezone());
                }
                $this->mailDAO->save($email);
                return $rc;
            }
            return FALSE;
        }
        throw new EmailException('No DAO available. Cannot retrieve e-mail by ID.');
    }

    /**
     * Creates a new Email object that reflects the MailMode settings.
     */
    public function getReconfiguredEmail(Email $email) {
        $rc = new Email();
        
        if ($email->getSender() != NULL) {
            $rc->setSender($email->getSender());
        } else {
            $rc->setSender($this->config->getDefaultSender());
        }
        $rc->setReplyTo($email->getReplyTo());
        $rc->addAttachments($email->getAttachments());
        $rc->setBody(Email::TEXT, $email->getBody(Email::TEXT));
        $rc->setBody(Email::HTML, $email->getBody(Email::HTML));
        
        if ($this->config->getMailMode() == EmailQueue::REROUTE) {
            $rc->setSubject($this->config->getRerouteConfig()->getSubjectPrefix().$this->config->getSubjectPrefix().$email->getSubject().' - '.$email->stringify($email->getTo()));
            $rc->addTo($this->config->getRerouteConfig()->getRecipients());
        } else {
            $rc->setSubject($this->config->getSubjectPrefix().$email->getSubject());
            $rc->addTo($email->getTo());
            $rc->addCc($email->getCc());
            $rc->addBcc($email->getBcc());
            if ($this->config->getMailMode() == EmailQueue::BCC) {
                $rc->addBcc($this->config->getBccConfig()->getRecipients());
            }
        }
        return $rc;
    }
    
    public function send(Email $email) {
        // Modify mail according to sending mode
        $email = $this->getReconfiguredEmail($email);
        return $this->_send($email);
    }
    
    /**
     * Synchronously send email object.
     */
    protected function _send(Email $email) {
        // Start
        $phpMailer = $this->getMailer();
        
        // Sender
        $phpMailer->setFrom($email->getSender()->email, $email->getSender()->name);
        
        // Reply-To
        if ($email->getReplyTo() != NULL) {
            $phpMailer->addReplyTo($email->getReplyTo()->email, $email->getReplyTo()->name);
        }
        
        // Recipients
        foreach ($email->getTo() as $recipient) {
            $phpMailer->addAddress($recipient->email, $recipient->name);
        }
        foreach ($email->getCc() as $recipient) {
            $phpMailer->addCC($recipient->email, $recipient->name);
        }
        foreach ($email->getBcc() as $recipient) {
            $phpMailer->addBCC($recipient->email, $recipient->name);
        }

        // Subject
        $phpMailer->Subject = '=?utf-8?B?' . base64_encode($email->getSubject()) . '?=';
        
        // Body
        if ($email->getBody(Email::HTML) != NULL) {
            $phpMailer->isHTML(true);
            $phpMailer->Body = $email->getBody(Email::HTML);
            if ($email->getBody(Email::TEXT) != NULL) {
                $phpMailer->AltBody = $email->getBody(Email::TEXT);
            }
        } else {
            $phpMailer->Body = $email->getBody(Email::TEXT);
        }
        
        // Attachments
        foreach ($email->getAttachments() as $a) {
            if ($a->type == Attachment::ATTACHED) {
                $phpMailer->AddAttachment($a->path, $a->name, 'base64', $a->mimeType);
            } else if ($a->type == 'embedded') {
                $phpMailer->AddEmbeddedImage($a->path, $a->cid, $a->name);
            }
        }

        $rc = TRUE;
        if ($this->config->getMailMode() != EmailQueue::BLOCK) {
            $rc = $phpMailer->send();
            Log::debug('Mail sent: '.$email->getLogString());
            if (!$rc) {
                Log::error("Mailer Error: " . $phpMailer->ErrorInfo);
            } else {
                foreach ($email->getAttachments() as $a) {
                    if ($a->deleteAfterSent) {
                        unlink($a->path);
                    }
                }
            }
        }
        return $rc;
    }

    /**
     * Queues a single email or multiple emails.
     * <p>The second parameter $recipients can be used with single Email object only.</p>
     * <p>Example of $recpients:</p>
     * <ul>
     * <li>list of list of recipients: <code>[ ["john.doe@example.com","john@example.com"], ["jane.doe@example.com"] ]</code></li>
     * <li>list of recipient objects: <code>[ {"to":"john.doe@example.com", "cc":"jane.doe@example.com"}, ... ]</code></li>
     * </ul>
     * @param mixed $email - single Email object or array of Email objects
     * @param array $recipients - list of recipients to send the same email. Can be a list of lists (TO addresses)
     *    or a list of objects with to, cc or bcc attributes that define the recipients.
     * @return TRUE when email was queued
     */
    public function queue($email, $recipients = NULL) {
        if (is_a($email, 'TgEmail\\Email')) {
            if ($recipients == NULL) {
                // Single Email to be sent
                // Modify mail according to sending mode
                $email = $this->getReconfiguredEmail($email);
                return $this->_queue($email);
            }
            // Single email with multiple recipient definitions
            foreach ($recipients AS $def) {
                if (is_array($def)) {
                    // All TO addresses
                    $email->recipients = NULL;
                    $email->addTo($def);
                    $this->queue($email);
                } else {
                    $email->recipients = NULL;
                    if (isset($def->to))  $email->addTo($def->to);
                    if (isset($def->cc))  $email->addCc($def->cc);
                    if (isset($def->bcc)) $email->addBcc($def->bcc);
                    $this->queue($email);
                }
            }
        } else if (is_array($email)) {
            foreach ($email AS $m) {
                $this->queue($m);
            }
        } else {
            throw new EmailException('Cannot queue: $email must be array of Email or single Email object');
        }
    }
    
    /**
     * Queues an email.
     *
     * @param Email $email
     *            - \WebApp\Email object
     * @return true when e-mail was queued
     */
    protected function _queue($email) {
        if ($this->mailDAO != NULL) {
            if ($this->config->getMailMode() != EmailQueue::BLOCK) {
                $email->queued_time     = new Date(time(), $this->config->getTimezone());
                $email->status          = Email::PENDING;
                $email->failed_attempts = 0;
                $email->sent_time       = NULL;
                $rc = $this->mailDAO->create($email);
                return is_int($rc);
            }
            return TRUE;
        }
        throw new EmailException('Queueing is not supported. No DAO available.');
    }

}

