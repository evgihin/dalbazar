<?php

/**
 * Email class file.
 * 
 * @author Jonah Turnquist <poppitypop@gmail.com>
 * @link http://php-thoughts.cubedwater.com/
 * @version 1.0
 */
class Email extends CApplicationComponent {

    /**
     * @var string Type of email.  Options include "text/html" and "text/plain"
     */
    public $type = 'text/html';

    /**
     * @var string Receiver, or receivers of the mail.
     */
    public $to = null;

    /**
     * @var string Email subject
     */
    public $subject = '';

    /**
     * @var string from address
     */
    public $from = null;

    /**
     * @var string Reply-to address
     */
    public $replyTo = null;

    /**
     * @var string Return-path address
     */
    public $returnPath = null;

    /**
     * @var string Carbon Copy
     *
     * List of email's that should receive a copy of the email.
     * The Recipient WILL be able to see this list
     */
    public $cc = null;

    /**
     * @var string Blind Carbon Copy
     *
     * List of email's that should receive a copy of the email.
     * The Recipient WILL NOT be able to see this list
     */
    public $bcc = null;

    /**
     * @var string Main content
     */
    public $message = '';

    /**
     * @var string Delivery type.  If set to 'php' it will use php's mail() function, and if set to 'debug'
     * it will not actually send it but output it to the screen
     */
    public $delivery = 'php';

    /**
     * @var string language to encode the message in (eg "Japanese", "ja", "English", "en" and "uni" (UTF-8))
     */
    public $language = 'uni';

    /**
     * @var string the content-type of the email
     */
    public $contentType = 'utf-8';

    /**
     * @var string The view to use as the content of the email, as an alternative to setting $this->message.
     * Must be located in application.views.email directory.  This email object is availiable within the view
     * through $email, thus letting you define things such as the subject within the view (helps maintain 
     * seperation of logic and output).
     */
    public $view = null;

    /**
     * @var array Variable to be sent to the view.
     */
    public $viewVars = array();

    /**
     * @var string The layout for the view to be imbedded in. Must be located in
     * application.views.email.layouts directory.  Not required even if you are using a view
     */
    public $layout = null;

    /**
     * @var integer line length of email as per RFC2822 Section 2.1.1
     */
    public $lineLength = 70;

    /**
     * @var int максимальное количество получателей в одном письме. Если больше - то делается рассылка через очередь 
     */
    public $maxRecipients = NULL;

    /**
     * @var int Количество доставляемых сообщений за один такт очереди 
     */
    public $mailsPerTakt = NULL;
    
    private $controller = NULL;

    public function __construct() {
        Yii::setPathOfAlias('email', dirname(__FILE__) . '/views');
        
        if (isset(Yii::app()->controller))
            $this->controller = Yii::app()->controller;
        else
            $this->controller = new CController('Site');
    }

    /**
     * Возвращает текст сообщения
     * @param mixed $args Либо массив параметров вида, либо текст сообщения. Если не указан, то всё берем из параметров класса
     * @return string html-код сообщения
     * @throws CException Вылетает если указано недостаточно данных
     */
    public function render($args = null) {
        $result = '';
        switch (gettype($args)) {
            case "NULL":
                $args = $this->viewVars; //break не забыт, так и надо!!
            case "array":
                if ($this->view !== null) {
                    $message = $this->controller->renderInternal(Yii::getPathOfAlias('application.views.email.' . $this->view).'.php', array_merge($args, array('email' => $this)), true);
                } else {
                    throw new CException("Не задан шаблон отображения");
                }
                break;
            case "string":
                $message = $args;
                break;
        }

        if (!$this->layout) {
            $result = $message;
        } else {
            $result = $this->controller->renderInternal(Yii::getPathOfAlias('application.views.email.layouts.' . $this->layout).'.php', array('content' => $message, 'email' => $this), true);
        }
        return $result;
    }

    /**
     * Отправляет письмо
     * @param mixed $args Либо массив параметров вида, либо текст сообщения. Если не указан, то всё берем из параметров класса
     * If not set, it will use $this->message instead for the content of the email
     */
    public function send($arg1 = null) {
        $to = $this->to;
        if (is_array($to)) {
            foreach ($to as $val) {
                $this->to = $val;
                $message = $this->render($arg1);
                if (count($to) > $this->maxRecipients) {
                    $this->addToQueue($message);
                } else {
                    $this->mail($this->to, $this->subject, $message);
                }
            }
            $this->to = $to;
            return true;
        } else {
            $message = $this->render($arg1);
            return $this->mail($this->to, $this->subject, $message);
        }
    }

    private function addToQueue($message) {
        Yii::app()->db->createCommand()->insert("mail_queue", array(
            "create_time" => time(),
            "time_to_send" => time(),
            "to" => $this->to,
            "subject" => $this->subject,
            "message" => $message,
            "headers" => implode("\r\n", $this->createHeaders())
        ));
    }
    
    public function sendQueue(){
        $messages = Yii::app()->db->createCommand()
                ->select()
                ->from("mail_queue")
                ->where("time_to_send <= :t AND sent_time IS NULL",array(":t"=>time()))
                ->limit($this->mailsPerTakt)
                ->queryAll();
        foreach ($messages as $message) {
            if ($this->mail($message['to'], $message['subject'], $message['message'], $message['headers']))
                    Yii::app()->db->createCommand()
                    ->update("mail_queue", array("sent_time"=>time()), "mail_queue_id=:id", array(":id"=>$message['mail_queue_id']));
        }
    }

    private function mail($to, $subject, $message, $headers=NULL) {
        switch ($this->delivery) {
            case 'php':
                $message = wordwrap($message, $this->lineLength);
                mb_language($this->language);
                return mb_send_mail($to, $subject, $message, (is_null($headers))?implode("\r\n", $this->createHeaders()):$headers);
            case 'debug':
                $debug = $this->controller->renderInternal(Yii::getPathOfAlias('email.debug').".php", array_merge(compact('to', 'subject', 'message'), array('headers' => $this->createHeaders())), true);
                $mails = Yii::app()->user->getState('d_email', array());
                array_unshift($mails, $debug);
                Yii::app()->user->setState('d_email', $mails);
                return true;
                break;
        }
    }

    private function createHeaders() {
        $headers = array();

//maps class variable names to header names
        $map = array(
            'from' => 'From',
            'cc' => 'Cc',
            'bcc' => 'Bcc',
            'replyTo' => 'Reply-To',
            'returnPath' => 'Return-Path',
        );
        foreach ($map as $key => $value) {
            if (isset($this->$key))
                $headers[] = "$value: {$this->processAddresses($this->$key)}";
        }
        $headers[] = "Content-Type: {$this->type}; charset=" . $this->contentType;
        $headers[] = "MIME-Version: 1.0";
        $headers[] = 'X-Mailer: PHP/' . phpversion();


        return $headers;
    }

    private function processAddresses($addresses) {
        return (is_array($addresses)) ? implode(', ', $addresses) : $addresses;
    }

}