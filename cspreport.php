<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use GuzzleHttp\Client;

require 'vendor/autoload.php';
$mail = new PHPMailer(true);

$raw =  file_get_contents('php://input');

try {
    //Server settings
    $mail->isSMTP();
    $mail->Host       = '127.0.0.1';
    $mail->SMTPAuth   = false;
    $mail->SMTPSecure = false;
    $mail->SMTPAutoTLS = false;
    $mail->Port       = 1025;

    //Recipients
    $mail->setFrom('cspreport@bienlab.com', 'CSP Report');
    $mail->addAddress('cspreport@bienlab.com', 'CSP Report');

    //Content
    $mail->isHTML(false);                       
    $mail->Subject = 'CSP Report for *.bienlab.com';
    $mail->Body    = prettyPrint($raw);
    $mail->send();
    slacksend(prettyPrint($raw));

   echo "Message has been sent. \n";
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
echo "Thank for reporting\n";
function prettyPrint( $json )
{
    $result = '';
    $level = 0;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;
    $json_length = strlen( $json );

    for( $i = 0; $i < $json_length; $i++ ) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if( $ends_line_level !== NULL ) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ( $in_escape ) {
            $in_escape = false;
        } else if( $char === '"' ) {
            $in_quotes = !$in_quotes;
        } else if( ! $in_quotes ) {
            switch( $char ) {
                case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                case '{': case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        } else if ( $char === '\\' ) {
            $in_escape = true;
        }
        if( $new_line_level !== NULL ) {
            $result .= "\n".str_repeat( "\t", $new_line_level );
        }
        $result .= $char.$post;
    }

    return $result;
}

function slacksend($text) {
$client = new GuzzleHttp\Client();

$response = $client->request(
    'POST',
    'https://hooks.slack.com/services/ratbimat/khongnoidau'
    [
        'json' => [
            'type' => 'mrkdwn',
            'text' => '```CSP report: ' . $text . '```'
        ]
    ]
);

$headers = $response->getHeaders();
$body = $response->getBody();

}
?>

