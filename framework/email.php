<?php

/*
 * Examples:
 *
 * $e = new Email;
 * $e->To('Dan L mardanlin@gmail.com')
 *   ->From('LuxDigital Support contact@luxdig.com')
 *   ->Subject('Testing new email object')
 *   ->Body('There have been some major changes. We increased our budget significantly. Would like to talk more.')
 *   ->Template('obj.email.generic');
 * $e->Send();
 *
 * Instead of specifying ->Template(), you can also do:
 *
 *   ->Body(outputUI(new UI('obj.email.generic', array(
 *      'SOME_ATTR'=>'Some val'
 *     ))))
 */


class Email {
    // Create SwiftMailer Object variables
    private $smTransporter, $smMailer, $smMessage;

    // Create account setting variables
    private $accountServer, $accountPort, $accountConnection, $accountEmail, $accountPassword;

    // Create email parameter variables
    private $emailTo, $emailBCC, $emailReplyTo, $emailSubject, $emailBody, $emailBodyWithTemplate, $emailContentType, $emailTemplate;

    // Debugging
    private $fullDataArray;


    function __construct($to=null,$replyTo=null,$subject=null,$body=null,$type='text/html') {
        // include SwiftMailer grunt file
        require_once dirname(__FILE__).'/../addons/swiftmailer/lib/swift_required.php';

        // Require the object autoload and utilities files just in case they haven't been loaded in
        require_once dirname(__FILE__).'/../autoload/init.php';

        // set default values for server/account
        $this->accountServer = EMAIL_AUTH_SERVER;
        $this->accountPort = EMAIL_AUTH_PORT;
        $this->accountConnection = EMAIL_AUTH_CONNECTION;
        $this->accountEmail = EMAIL_AUTH_ADDRESS;
        $this->accountPassword = EMAIL_AUTH_PASSWORD;

        $this->EstablishConnection();

        $fields = array('to', 'replyTo', 'subject', 'body', 'type');
        $issetCount = 0;
        foreach($fields as $f) {
            $attrName = 'email'.ucfirst($f);
            if ( $$f != null ) {
                $this->Set($attrName, $$f);
                $issetCount++;
            }
        }



        if ( $issetCount == sizeof($fields) ) {
            // all good to go - try to send email
        }

        // if all supplied values are valid, send email immediately, otherwise just assign the values for them
    }

    public function Send() {
        $this->PrepEmailAttributes();

        $this->smMessage = Swift_Message::newInstance($this->emailSubject)
            ->setContentType($this->emailContentType)
            ->setFrom([$this->emailReplyTo['email']=>$this->emailReplyTo['name']])
            ->setBCC(explode(',', $this->emailBCC))
            ->setReplyTo(array($this->emailReplyTo['email']=>$this->emailReplyTo['name']))
            ->setTo(array($this->emailTo['email']=>$this->emailTo['name']))
            ->setBody($this->emailBodyWithTemplate);

        return $this->smMailer->send($this->smMessage);
    }

    public function Set($var, $val) {
        $var = $this->FindVar($var);

        if ( !$var )
            return false;

        // now set the var
        $this->$var = $val;

        return $this;
    }

    public function To($to) {
        return $this->Set('to', $to);
    }

    public function From($from) {
        return $this->Set('from', $from);
    }
    public function ReplyTo($replyTo) {
        return $this->Set('replyto', $replyTo);
    }

    public function Subject($subject) {
        return $this->Set('subject', $subject);
    }

    public function Body($body) {
        return $this->Set('body', $body);
    }

    public function Template($template) {
        return $this->Set('template', $template);
    }



    private function PrepEmailAttributes() {
        // test to/from make sure they're valid
        $this->emailTo = $this->ParseRecipient($this->emailTo);
        $this->emailReplyTo = $this->ParseRecipient($this->emailReplyTo);

        $this->emailBodyWithTemplate = $this->emailBody;
        // check if template is present
        if ( $this->emailTemplate != '' )
            $this->emailBodyWithTemplate = UI($this->emailTemplate, [
                'EMAIL_AUTH_TO'=>$this->emailTo['email'],
                'EMAIL_AUTH_TO_NAME'=>($this->emailTo['name']=='')?'there':$this->emailTo['name'], // Hey there, OR Hey Dan,
                'EMAIL_AUTH_FROM'=>$this->emailReplyTo['email'],
                'EMAIL_AUTH_FROM_NAME'=>$this->emailReplyTo['name'],
                'EMAIL_AUTH_FROM_ICON'=>$this->emailReplyTo['icon'],
                'EMAIL_AUTH_FROM_ICON_VISIBILITY'=>($this->emailReplyTo['icon']=='')?'display: none;':'',
                'EMAIL_AUTH_FROM_ICON_COMMENT_START'=>($this->emailReplyTo['icon']=='')?'<!--':'',
                'EMAIL_AUTH_FROM_ICON_COMMENT_END'=>($this->emailReplyTo['icon']=='')?'-->':'',
                'EMAIL_AUTH_SUBJECT'=>$this->emailSubject,
                'EMAIL_AUTH_BODY'=>preg_replace('#\n#', '<br />', trim($this->emailBody))
            ]);

        // for debugging
        $this->fullDataArray = [
            'email-to'=>$this->emailTo,
            'email-reply-to'=>$this->emailReplyTo,
            'email-subject'=>$this->emailSubject,
            'email-body'=>$this->emailBody,
            'email-body-with-template'=>$this->emailBodyWithTemplate,
            'email-template'=>$this->emailTemplate,
            'server-url'=>$this->accountServer,
            'server-port'=>$this->accountPort,
            'server-connection'=>$this->accountConnection,
            'server-email'=>$this->accountEmail,
            'server-password'=>$this->accountPassword
        ];

        // make sure no required values are blank
    }

    private function EstablishConnection() {
        $this->smTransporter = Swift_SmtpTransport::newInstance($this->accountServer, $this->accountPort, $this->accountConnection)
            ->setUsername($this->accountEmail)
            ->setPassword($this->accountPassword);

        $this->smMailer = Swift_Mailer::newInstance($this->smTransporter);
    }

    private function ParseRecipient($recipient) {
        /*
         * Accepted encodings:
         *      array('name'=>'email')
         *      name:email:icon
         *      name|email|icon
         *      {} or [] or <> + above 2
         */

        $rEmail = $this->ExtractEmails($recipient);
        $rName = '';
        $rIcon = '';

        if ( is_array($recipient) ) {
            $rName = array_search($rEmail, $recipient);
            if ( isset($recipient['name']) )
                $rName = $recipient['name'];
            if ( isset($recipient['icon']) )
                $rIcon = $recipient['icon'];
        } else {
            // strip potential delimeters
            $r = preg_replace('#^(\{\[\<)([^\}\]\>]+)(\}\]\>)$#', '$2', $recipient);

            // replace all separators with :
            $r = str_replace('|', ':', $r);

            // replace : and email to get name & potential icon leftover
            $rLeftovers = str_replace($rEmail, '', $r);

            // check for icon
            $iconRegex = '#[^ ]+\.(jpg|png|jpeg|bmp|gif)#i';
            $iconLeftovers = str_replace(':', ' ', $rLeftovers);
            if ( preg_match($iconRegex, $iconLeftovers) ) {
                // found one
                preg_match_all($iconRegex, $iconLeftovers, $iconMatches);
                $rIcon = @$iconMatches[0][0];
            }

            $rName = trim(str_replace(array($rEmail, $rIcon, ':'), '', $r));

        }



        return array('name'=>$rName, 'email'=>$rEmail, 'icon'=>$rIcon);

    }


    public function ExtractEmails($str) {
        $emails = [];
        foreach(explode(' ', $str) as $word) {
            if ( filter_var($word, FILTER_VALIDATE_EMAIL) )
                $emails[] = $word;
        }
        if ( sizeof($emails) == 0 )
            return false;

        return join(',', $emails);
    }

    private function FindVar($var) {
        $acceptedNames = array(
            'emailTo'=>array(
                'to',
                'recipient'
            ),
            'emailBCC'=>array(
                'bcc'
            ),
            'emailReplyTo'=>array(
                'from',
                'reply',
                'replyto'
            ),
            'emailSubject'=>array(
                'subject',
                'sub',
                'title',
                'headline'
            ),
            'emailBody'=>array(
                'body',
                'message',
                'details'
            ),
            'emailContentType'=>array(
                'html',
                'plaintext',
                'plain',
                'encoding',
                'style',
                'type',
                'emailtype',
                'contenttype'
            ),
            'emailTemplate'=>array(
                'template',
                'theme'
            )
        );

        $var = strtolower(preg_replace('#[^a-z]#i', '', $var));

        // now search for it
        $field = false;
        foreach($acceptedNames as $fieldName=>$names) {
            foreach($names as $name) {
                if ( $name == $var )
                    $field = $fieldName;
            }
        }

        if ( !$field )
            return false;

        return $field;
    }


    function __destruct() {

    }
}

?>
