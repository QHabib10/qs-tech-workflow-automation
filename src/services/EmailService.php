<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;
    private $conn;
    
    public function __construct($conn = null) {
        $this->mailer = new PHPMailer(true);
        $this->conn = $conn;
        $this->configureSMTP();
    }
    
    private function configureSMTP() {
       
        $this->mailer->isSMTP();
        $this->mailer->Host = getenv('SMTP_HOST');
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = getenv('SMTP_USER');
        $this->mailer->Password = getenv('SMTP_PASS');
        $this->mailer->SMTPSecure = getenv('SMTP_SECURE');
        $this->mailer->Port = (int)getenv('SMTP_PORT');
        
        $this->mailer->setFrom(getenv('FROM_EMAIL'), getenv('FROM_NAME'));
        $this->mailer->addReplyTo(getenv('REPLY_TO') ?: getenv('FROM_EMAIL'));
    }
    
    public function sendAutoReply($toName, $toEmail, $keywords = [], $leadId = null) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail, $toName);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Welcome to QS Tech';
            
            $htmlBody = $this->getAutoReplyTemplate($toName, $keywords);
            $textBody = $this->getAutoReplyTextTemplate($toName, $keywords);
            
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = $textBody;
            
            $result = $this->mailer->send();
            
            $this->logEmail('auto_reply_sent', $toEmail, 'success', $leadId);
            
            return $result;
        } catch (Exception $e) {
           
            $this->logEmail('auto_reply_failed', $toEmail, $e->getMessage(), $leadId);
            return false;
        }
    }
    
    private function getAutoReplyTemplate($name, $keywords) {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <p>Hi {$name}, thanks—your request is received. We'll reply within 1 business day.</p>
        </div>
        ";
    }
    
    private function getAutoReplyTextTemplate($name, $keywords) {
        return "Hi {$name}, thanks—your request is received. We'll reply within 1 business day.";
    }

    // Send low stock notification to PM
    public function sendLowStockNotification($itemName, $qty, $threshold) {
        try {
            $pmEmail = getenv('PROCUREMENT_PM_EMAIL') ?: getenv('REPLY_TO') ?: getenv('FROM_EMAIL');
            if (!$pmEmail) {
                return false;
            }

            $this->mailer->clearAddresses();
            $this->mailer->addAddress($pmEmail);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = "Low stock Alert!";

            $html = "<p>Low stock detected for <strong>{$itemName}</strong>.</p>"
                  . "<p>Current qty: <strong>{$qty}</strong> / Threshold: <strong>{$threshold}</strong></p>"
                  . "<p>Please review and reorder as needed.</p>";
            $this->mailer->Body = $html;
            $this->mailer->AltBody = "Low stock: {$itemName} ({$qty}/{$threshold}). Please review and reorder.";

            $result = $this->mailer->send();
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function logEmail($action, $email, $status, $leadId = null) {
       
        if ($this->conn) {
            $stmt = $this->conn->prepare("INSERT INTO audit_log (action, entity, entity_id, details) VALUES (?, 'leads', ?, ?)");
            $details = json_encode(['email' => $email, 'status' => $status]);
            $stmt->bind_param('sis', $action, $leadId, $details);
            $stmt->execute();
            $stmt->close();
        }
    }
}
