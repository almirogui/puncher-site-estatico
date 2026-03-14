<?php
/**
 * PUNCHER.COM - Contact Form Handler
 * Receives contact messages and sends email notification
 */

header('Content-Type: application/json');

// Configuration
$to_email = 'puncher@puncher.com';
$subject_prefix = '[Puncher.com Contact]';

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$user_message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validate required fields
if (empty($name) || empty($email) || empty($subject) || empty($user_message)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
    exit;
}

// Build email content
$email_subject = "$subject_prefix " . htmlspecialchars($subject);

$message = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #1a365d 0%, #2b6cb0 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f7fafc; padding: 30px; border: 1px solid #e2e8f0; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #1a365d; }
        .value { margin-top: 5px; padding: 10px; background: white; border-radius: 5px; border: 1px solid #e2e8f0; }
        .footer { text-align: center; padding: 20px; color: #718096; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>📧 New Contact Message</h2>
        </div>
        <div class='content'>
            <div class='field'>
                <div class='label'>👤 Name:</div>
                <div class='value'>" . htmlspecialchars($name) . "</div>
            </div>
            <div class='field'>
                <div class='label'>📧 Email:</div>
                <div class='value'><a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a></div>
            </div>
            <div class='field'>
                <div class='label'>📌 Subject:</div>
                <div class='value'>" . htmlspecialchars($subject) . "</div>
            </div>
            <div class='field'>
                <div class='label'>💬 Message:</div>
                <div class='value'>" . nl2br(htmlspecialchars($user_message)) . "</div>
            </div>
        </div>
        <div class='footer'>
            <p>This message was sent from the Puncher.com website.</p>
        </div>
    </div>
</body>
</html>
";

// Email headers
$headers = [
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=UTF-8',
    'From: Puncher.com <noreply@puncher.com>',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion()
];

// Send email
$email_sent = mail($to_email, $email_subject, $message, implode("\r\n", $headers));

if ($email_sent) {
    // Log the request
    $log_entry = date('Y-m-d H:i:s') . " | Contact | $name | $email | $subject\n";
    file_put_contents('../logs/contact.log', $log_entry, FILE_APPEND);
    
    echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send email. Please try again or contact us directly.']);
}
