<?php
namespace TriTan\Common;

use TriTan\Interfaces\MailerInterface;
use Qubus\Hooks\ActionFilterHook;
use Qubus\Exception\Error;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Cascade\Cascade;

/**
 * Mailer Class. Inspired by WordPress' wp_mail() function.
 *
 * @license GPLv3
 *
 * @since 1.0.0
 * @package Qubus CMS
 * @author Joshua Parker <josh@joshuaparker.blog>
 */
final class Mailer implements MailerInterface
{
    protected $mailer;
    protected $app;
    protected $hook;

    public function __construct()
    {
        $this->mailer = new PHPMailer();
        $this->hook = ActionFilterHook::getInstance();
    }

    /**
     * Borrowed from WordPress
     *
     * Send mail, similar to PHP's mail
     * A true return value does not automatically mean that the user received the
     * email successfully. It just only means that the method used was able to
     * process the request without any errors.
     *
     * @since 1.0.0
     * @param string $to
     *            Array or comma-separated list of email addresses to send message.
     * @param string $subject
     *            Subject of the email.
     * @param mixed $message
     *            The body of the email.
     * @param mixed $headers
     *            Email headers sent.
     * @param mixed $attachments
     *            Attachments to be sent with the email.
     * @return mixed
     */
    public function mail($to, $subject, $message, $headers = '', $attachments = [])
    {
        $charset = 'UTF-8';

        /**
         * Filter the Mailer::mail() arguments.
         *
         * @since 1.0.0
         * @param array $args
         *            A compacted array of ttcmsMail() arguments, including the "to" email,
         *            subject, message, headers, and attachments values.
         */
        $atts = $this->hook->applyFilter('ttcms_mail', compact('to', 'subject', 'message', 'headers', 'attachments'));

        if (isset($atts['to'])) {
            $to = $atts['to'];
        }
        if (!is_array($to)) {
            $to = explode(',', $to);
        }
        if (isset($atts['subject'])) {
            $subject = $atts['subject'];
        }
        if (isset($atts['message'])) {
            $message = $atts['message'];
        }
        if (isset($atts['headers'])) {
            $headers = $atts['headers'];
        }
        if (isset($atts['attachments'])) {
            $attachments = $atts['attachments'];
        }

        if (!is_array($attachments)) {
            $attachments = explode("\n", str_replace("\r\n", "\n", $attachments));
        }

        // Headers
        $cc = $bcc = $reply_to = [];

        // Headers
        if (empty($headers)) {
            $headers = [];
        } else {
            if (!is_array($headers)) {
                // Explode the headers out, so this function can take both
                // string headers and an array of headers.
                $tempheaders = explode("\n", str_replace("\r\n", "\n", $headers));
            } else {
                $tempheaders = $headers;
            }

            $headers = [];

            // If it's actually got contents
            if (!empty($tempheaders)) {
                // Iterate through the raw headers
                foreach ((array) $tempheaders as $header) {
                    if (strpos($header, ':') === false) {
                        if (false !== stripos($header, 'boundary=')) {
                            $parts = preg_split('/boundary=/i', trim($header));
                            $boundary = trim(str_replace([
                                "'",
                                '"'
                                            ], '', $parts[1]));
                        }
                        continue;
                    }
                    // Explode them out
                    list($name, $content) = explode(':', trim($header), 2);
                    // Cleanup crew
                    $name = trim($name);
                    $content = trim($content);
                    switch (strtolower($name)) {
                        // Mainly for legacy -- process a From: header if it's there
                        case 'from':
                            $bracket_pos = strpos($content, '<');
                            if ($bracket_pos !== false) {
                                // Text before the bracketed email is the "From" name.
                                if ($bracket_pos > 0) {
                                    $from_name = substr($content, 0, $bracket_pos - 1);
                                    $from_name = str_replace('"', '', $from_name);
                                    $from_name = trim($from_name);
                                }
                                $from_email = substr($content, $bracket_pos + 1);
                                $from_email = str_replace('>', '', $from_email);
                                $from_email = trim($from_email);
                            // Avoid setting an empty $from_email.
                            } elseif ('' !== trim($content)) {
                                $from_email = trim($content);
                            }
                            break;
                        case 'content-type':
                            if (strpos($content, ';') !== false) {
                                list($type, $charset_content) = explode(';', $content);
                                $content_type = trim($type);
                                if (false !== stripos($charset_content, 'charset=')) {
                                    $charset = trim(str_replace([
                                        'charset=',
                                        '"'
                                                    ], '', $charset_content));
                                } elseif (false !== stripos($charset_content, 'boundary=')) {
                                    $boundary = trim(str_replace([
                                        'BOUNDARY=',
                                        'boundary=',
                                        '"'
                                                    ], '', $charset_content));
                                    $charset = '';
                                }
                                // Avoid setting an empty $content_type.
                            } elseif ('' !== trim($content)) {
                                $content_type = trim($content);
                            }
                            break;
                        case 'cc':
                            $cc = array_merge((array) $cc, explode(',', $content));
                            break;
                        case 'bcc':
                            $bcc = array_merge((array) $bcc, explode(',', $content));
                            break;
                        case 'reply-to':
                            $reply_to = array_merge((array) $reply_to, explode(',', $content));
                            break;
                        default:
                            // Add it to our grand headers array
                            $headers[trim($name)] = trim($content);
                            break;
                    }
                }
            }
        }

        // Empty out the values that may be set
        $this->mailer->clearAllRecipients();
        $this->mailer->clearAttachments();
        $this->mailer->clearCustomHeaders();
        $this->mailer->clearReplyTos();

        // From email and name
        // If we don't have a name from the input headers
        if (!isset($from_name)) {
            $from_name = 'TriTan CMS';
        }

        $server_name = strtolower($_SERVER['SERVER_NAME']);
        if (substr($server_name, 0, 4) == 'www.') {
            $server_name = substr($server_name, 4);
        }

        if (!isset($from_email)) {
            $from_email = 'tritan-cms@' . $server_name;
        }

        /**
         * Filter the email address to send from.
         *
         * @since 1.0.0
         * @param string $from_email Email address to send from.
         */
        $from_email = $this->hook->applyFilter('ttcms_mail_from', $from_email);

        /**
         * Filter the name to associate with the "from" email address.
         *
         * @since 1.0.0
         * @param string $from_name Name associated with the "from" email address.
         */
        $from_name = $this->hook->applyFilter('ttcms_mail_from_name', $from_name);

        try {
            $this->mailer->setFrom($from_email, $from_name, false);
        } catch (Exception $e) {
            $mail_error_data = compact('to', 'subject', 'message', 'headers', 'attachments');
            $mail_error_data['phpmailer_exception_code'] = $e->getCode();
            $this->hook->doAction('ttcms_mail_failed', new Error('ttcms_mail_failed', $e->getMessage(), $mail_error_data));
            return false;
        }

        foreach ((array) $to as $recipient) {
            try {
                // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                $recipient_name = '';
                if (preg_match('/(.*)<(.+)>/', $recipient, $matches)) {
                    if (count($matches) == 3) {
                        $recipient_name = $matches[1];
                        $recipient = $matches[2];
                    }
                }
                $this->mailer->addAddress($recipient, $recipient_name);
            } catch (Exception $e) {
                Cascade::getLogger('error')->error(sprintf('PHPMailer[%s]: Error: %s', $e->getCode(), $e->getMessage()));
                continue;
            }
        }

        // Set mail's subject and body
        $this->mailer->Subject = $subject;
        $this->mailer->Body = $message;

        // Set destination addresses, using appropriate methods for handling addresses
        $address_headers = compact('to', 'cc', 'bcc', 'reply_to');

        foreach ($address_headers as $address_header => $addresses) {
            if (empty($addresses)) {
                continue;
            }
            foreach ((array) $addresses as $address) {
                try {
                    // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                    $recipient_name = '';
                    if (preg_match('/(.*)<(.+)>/', $address, $matches)) {
                        if (count($matches) == 3) {
                            $recipient_name = $matches[1];
                            $address = $matches[2];
                        }
                    }
                    switch ($address_header) {
                        case 'to':
                            $this->mailer->addAddress($address, $recipient_name);
                            break;
                        case 'cc':
                            $this->mailer->addCc($address, $recipient_name);
                            break;
                        case 'bcc':
                            $this->mailer->addBcc($address, $recipient_name);
                            break;
                        case 'reply_to':
                            $this->mailer->addReplyTo($address, $recipient_name);
                            break;
                    }
                } catch (Exception $e) {
                    continue;
                }
            }
        }

        // Set to use PHP's mail()
        $this->mailer->isMail();

        // Set Content-Type and charset
        // If we don't have a content-type from the input headers
        if (!isset($content_type)) {
            $content_type = 'text/plain';
        }

        /**
         * Filter the Mailer::mail() content type.
         *
         * @since 1.0.0
         * @param string $content_type Default Mailer::mail() content type.
         */
        $content_type = $this->hook->applyFilter('ttcms_mail_content_type', $content_type);

        $this->mailer->ContentType = $content_type;

        // Set whether it's plaintext, depending on $content_type
        if ('text/html' == $content_type) {
            $this->mailer->isHTML(true);
        }

        // If we don't have a charset from the input headers
        if (!isset($charset)) {
            $charset = ''; //get_siteinfo( 'charset' );
        }

        // Set the content-type and charset

        /**
         * Filter the default Mailer::mail() charset.
         *
         * @since 1.0.0
         * @param string $charset Default email charset.
         */
        $this->mailer->CharSet = $this->hook->applyFilter('ttcms_mail_charset', $charset);

        // Set custom headers
        if (!empty($headers)) {
            foreach ((array) $headers as $name => $content) {
                $this->mailer->addCustomHeader(sprintf('%1$s: %2$s', $name, $content));
            }

            if (false !== stripos($content_type, 'multipart') && !empty($boundary)) {
                $this->mailer->addCustomHeader(sprintf("Content-Type: %s;\n\t boundary=\"%s\"", $content_type, $boundary));
            }
        }

        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                try {
                    $this->mailer->addAttachment($attachment);
                } catch (Exception $e) {
                    Cascade::getLogger('error')->error(sprintf('PHPMailer[%s]: Error: %s', $e->getCode(), $e->getMessage()));
                    continue;
                }
            }
        }

        /**
         * Fires after PHPMailer is initialized.
         *
         * @since 1.0.0
         * @param PHPMailer $this->mailer The PHPMailer instance, passed by reference.
         */
        $this->hook->doActionRefArray('ttcmsMailer_init', [
            &$this->mailer
        ]);

        // Send!
        try {
            return $this->mailer->send();
        } catch (Exception $e) {
            $mail_error_data = compact($to, $subject, $message, $headers, $attachments);
            $mail_error_data['phpmailer_exception_code'] = $e->getCode();
            /**
             * Fires after a \PHPMailer\PHPMailer\Exception is caught.
             *
             * @since 1.0.0
             * @param Error $error
             *            A Error object with the \PHPMailer\PHPMailer\Exception code, message, and an array
             *            containing the mail recipient, subject, message, headers, and attachments.
             */
            $this->hook->doAction('ttcms_mail_failed', new Error('ttcms_mail_failed', $e->getMessage(), $mail_error_data));
            Cascade::getLogger('error')->error(sprintf('PHPMailer[%s]: Error: %s', $e->getCode(), $e->getMessage()));
            return false;
        }
    }
}
