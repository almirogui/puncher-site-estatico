<?php
/**
 * PUNCHER.COM - Quote Form Handler
 * Receives quote requests and sends email notification
 * 
 * Features:
 * - Saves uploaded file and creates clickable link
 * - Sends notification to admin
 * - Sends confirmation email to customer
 */

header('Content-Type: application/json');

// Configuration
$to_email = 'puncher@puncher.com';
$from_email = 'noreply@puncher.com';
$site_url = 'https://puncher.com'; // Change to staging.puncher.com for testing
$subject_prefix = '[Puncher.com Quote Request]';

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$company = isset($_POST['company']) ? trim($_POST['company']) : '';
$service = isset($_POST['service']) ? trim($_POST['service']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$width = isset($_POST['width']) ? trim($_POST['width']) : '';
$height = isset($_POST['height']) ? trim($_POST['height']) : '';
$unit = isset($_POST['unit']) ? trim($_POST['unit']) : '';

// Validate required fields
if (empty($name) || empty($email) || empty($service) || empty($description)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
    exit;
}

// Validate dimensions
$has_width = !empty($width) && floatval($width) > 0;
$has_height = !empty($height) && floatval($height) > 0;
$has_dimension = $has_width || $has_height;

if (($service === 'digitizing' || $service === 'both') && !$has_dimension) {
    echo json_encode(['success' => false, 'message' => 'Please enter at least one dimension']);
    exit;
}

if ($has_dimension && (empty($unit) || !in_array($unit, ['cm', 'inches']))) {
    echo json_encode(['success' => false, 'message' => 'Please select a unit of measurement']);
    exit;
}

$unit_label = ($unit === 'cm') ? 'cm' : 'inches';

// Format dimensions for emails
$dimensions_text = 'Not provided';
if ($has_dimension) {
    $dimensions_parts = [];
    if ($has_width) $dimensions_parts[] = "Width: {$width} {$unit_label}";
    if ($has_height) $dimensions_parts[] = "Height: {$height} {$unit_label}";
    $dimensions_text = implode(' × ', $dimensions_parts);
}

// Service type labels
$service_labels = [
    'digitizing' => 'Embroidery Digitizing',
    'vector' => 'Vector Art',
    'both' => 'Both Services'
];
$service_label = isset($service_labels[$service]) ? $service_labels[$service] : $service;

// Handle file uploads (up to 3)
$allowed_types = [
    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
    'application/pdf', 'application/postscript',
    'image/svg+xml', 'application/illustrator'
];
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'ai', 'eps', 'svg'];

$file_links_html = '';
$files_for_customer = [];

foreach (['file1', 'file2', 'file3'] as $field) {
    if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES[$field];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ((in_array($file['type'], $allowed_types) || in_array($file_extension, $allowed_extensions))
            && $file['size'] <= 10 * 1024 * 1024) {

            $upload_dir = dirname(__DIR__) . '/uploads/quotes/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $unique_id = date('Ymd_His') . '_' . uniqid();
            $safe_filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
            $filename = $unique_id . '_' . $safe_filename;
            $filepath = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $file_url = $site_url . '/uploads/quotes/' . $filename;
                $file_links_html .= "
                <p>
                    <a href='{$file_url}' target='_blank' style='color: #2b6cb0; text-decoration: none; font-weight: bold;'>
                        📎 {$file['name']}
                    </a>
                    <br>
                    <a href='{$file_url}' target='_blank' style='color: #718096; font-size: 12px;'>
                        Click to view/download
                    </a>
                </p>
                ";
                $files_for_customer[] = $file['name'];
            }
        }
    }
}

$file_link_html = !empty($file_links_html) ? $file_links_html : '<p style="color: #999;">No file attached</p>';
$file_info_for_customer = $files_for_customer;

// Build email content for ADMIN
$subject = "$subject_prefix New Quote from $name";

$admin_message = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #1a365d 0%, #2b6cb0 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f7fafc; padding: 30px; border: 1px solid #e2e8f0; }
        .field { margin-bottom: 20px; }
        .label { font-weight: bold; color: #1a365d; margin-bottom: 5px; }
        .value { padding: 12px; background: white; border-radius: 5px; border: 1px solid #e2e8f0; }
        .footer { text-align: center; padding: 20px; color: #718096; font-size: 12px; }
        .file-box { background: #edf2f7; padding: 15px; border-radius: 8px; border-left: 4px solid #ed8936; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>📨 New Quote Request</h2>
        </div>
        <div class='content'>
            <div class='field'>
                <div class='label'>👤 Name:</div>
                <div class='value'>" . htmlspecialchars($name) . "</div>
            </div>
            <div class='field'>
                <div class='label'>📧 Email:</div>
                <div class='value'><a href='mailto:" . htmlspecialchars($email) . "' style='color: #2b6cb0;'>" . htmlspecialchars($email) . "</a></div>
            </div>
            <div class='field'>
                <div class='label'>🏢 Company:</div>
                <div class='value'>" . (empty($company) ? '<em style=\"color: #999;\">Not provided</em>' : htmlspecialchars($company)) . "</div>
            </div>
            <div class='field'>
                <div class='label'>🎯 Service Type:</div>
                <div class='value'><strong>" . htmlspecialchars($service_label) . "</strong></div>
            </div>
            <div class='field'>
                <div class='label'>📐 Dimensions:</div>
                <div class='value'><strong>{$dimensions_text}</strong></div>
            </div>
            <div class='field'>
                <div class='label'>📝 Project Description:</div>
                <div class='value'>" . nl2br(htmlspecialchars($description)) . "</div>
            </div>
            <div class='field'>
                <div class='label'>📎 Attached Files:</div>
                <div class='file-box'>
                    {$file_link_html}
                </div>
            </div>
        </div>
        <div class='footer'>
            <p>This quote request was sent from the Puncher.com website.</p>
            <p>Reply directly to this email to respond to the customer.</p>
        </div>
    </div>
</body>
</html>
";

// Email headers for ADMIN
$admin_headers = [
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=UTF-8',
    'From: Puncher.com <' . $from_email . '>',
    'Reply-To: ' . $name . ' <' . $email . '>',
    'X-Mailer: PHP/' . phpversion()
];

// Send email to ADMIN
$admin_email_sent = mail($to_email, $subject, $admin_message, implode("\r\n", $admin_headers));

// Build CONFIRMATION email for CUSTOMER
$customer_subject = "Your Quote Request has been Received - Puncher.com";

$file_attachment_info = '';
if (!empty($file_info_for_customer)) {
    $names = implode(', ', array_map('htmlspecialchars', $file_info_for_customer));
    $file_attachment_info = "
        <div style='background: #edf2f7; padding: 10px 15px; border-radius: 5px; margin: 15px 0;'>
            <strong>📎 Attached files:</strong> " . $names . "
        </div>
    ";
}

$customer_message = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #1a365d 0%, #2b6cb0 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .header h2 { margin: 0; }
        .content { background: #f7fafc; padding: 30px; border: 1px solid #e2e8f0; }
        .highlight { background: #c6f6d5; padding: 15px; border-radius: 8px; border-left: 4px solid #38a169; margin: 20px 0; }
        .summary { background: white; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #718096; font-size: 12px; background: #1a365d; border-radius: 0 0 10px 10px; }
        .footer p { margin: 5px 0; color: #a0aec0; }
        .footer a { color: #ed8936; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>✅ Quote Request Received!</h2>
        </div>
        <div class='content'>
            <p>Dear <strong>" . htmlspecialchars($name) . "</strong>,</p>
            
            <div class='highlight'>
                <strong>🎉 Great news!</strong> We have successfully received your quote request and our team is already reviewing it.
            </div>
            
            <p>You can expect a response from us within the <strong>next few hours</strong> during our business hours.</p>
            
            <div class='summary'>
                <h4 style='margin-top: 0; color: #1a365d;'>📋 Your Request Summary:</h4>
                <p><strong>Service:</strong> " . htmlspecialchars($service_label) . "</p>
                <p><strong>Dimensions:</strong> {$dimensions_text}</p>
                <p><strong>Description:</strong><br>" . nl2br(htmlspecialchars($description)) . "</p>
                {$file_attachment_info}
            </div>
            
            <p>If you have any additional information or questions, feel free to reply to this email or contact us at <a href='mailto:puncher@puncher.com' style='color: #2b6cb0;'>puncher@puncher.com</a>.</p>
            
            <p style='margin-top: 30px;'>Thank you for choosing Puncher.com!</p>
            
            <p>Best regards,<br>
            <strong>The Puncher Team</strong></p>
        </div>
        <div class='footer'>
            <p><strong>Puncher.com</strong> - Professional Embroidery Digitizing since 1993</p>
            <p>📧 <a href='mailto:puncher@puncher.com'>puncher@puncher.com</a> | 💬 <a href='https://wa.me/5531920039974' style='color: #ed8936;'>WhatsApp</a></p>
            <p><a href='https://puncher.com'>www.puncher.com</a></p>
        </div>
    </div>
</body>
</html>
";

// Email headers for CUSTOMER
$customer_headers = [
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=UTF-8',
    'From: Puncher.com <' . $from_email . '>',
    'Reply-To: ' . $to_email,
    'X-Mailer: PHP/' . phpversion()
];

// Send confirmation email to CUSTOMER
$customer_email_sent = mail($email, $customer_subject, $customer_message, implode("\r\n", $customer_headers));

// Log the request
$log_dir = dirname(__DIR__) . '/logs/';
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}
$log_entry = date('Y-m-d H:i:s') . " | Quote | $name | $email | $service | Admin:" . ($admin_email_sent ? 'OK' : 'FAIL') . " | Customer:" . ($customer_email_sent ? 'OK' : 'FAIL') . "\n";
file_put_contents($log_dir . 'quotes.log', $log_entry, FILE_APPEND);

// Response
if ($admin_email_sent) {
    echo json_encode([
        'success' => true, 
        'message' => 'Quote request sent successfully! Check your email for confirmation.'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to send email. Please try again or contact us directly at puncher@puncher.com'
    ]);
}
