<?php
/**
 * Общая отправка писем: SMTP (если включён) или mail().
 */

/**
 * SMTP (AUTH LOGIN + STARTTLS при tls). Возвращает true/false.
 */
function smtp_send_mail(array $cfg): bool
{
    $host = trim((string)($cfg['host'] ?? ''));
    $port = (int)($cfg['port'] ?? 587);
    $enc = strtolower(trim((string)($cfg['encryption'] ?? 'tls')));
    $username = (string)($cfg['username'] ?? '');
    $password = (string)($cfg['password'] ?? '');

    if ($host === '' || $port <= 0 || $username === '' || $password === '') {
        return false;
    }

    $fromEmail = (string)($cfg['from_email'] ?? '');
    $fromName = (string)($cfg['from_name'] ?? '');
    $toEmail = (string)($cfg['to_email'] ?? '');
    $subject = (string)($cfg['subject'] ?? '');
    $body = (string)($cfg['body'] ?? '');

    $remote = $host . ':' . $port;
    $verifyPeer = (bool)($cfg['verify_peer'] ?? true);
    $allowSelfSigned = (bool)($cfg['allow_self_signed'] ?? false);
    $debug = [
        'stage' => 'init',
        'remote' => $remote,
        'encryption' => $enc,
        'port' => $port,
    ];
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => $verifyPeer,
            'verify_peer_name' => $verifyPeer,
            'allow_self_signed' => $allowSelfSigned,
        ],
    ]);

    $transport = 'tcp';
    if ($enc === 'ssl') {
        $transport = 'ssl';
    }
    $debug['stage'] = 'connect';
    $fp = @stream_socket_client($transport . '://' . $remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
    if (!$fp) {
        $_SESSION['smtp_last_error'] = $debug + ['errno' => $errno ?? null, 'error' => $errstr ?? null];
        return false;
    }
    stream_set_timeout($fp, 15);

    $read = static function () use ($fp): string {
        $data = '';
        while (!feof($fp)) {
            $line = fgets($fp, 515);
            if ($line === false) {
                break;
            }
            $data .= $line;
            if (preg_match('/^\\d{3} /', $line)) {
                break;
            }
        }
        return $data;
    };

    $write = static function (string $cmd) use ($fp): void {
        fwrite($fp, $cmd . "\r\n");
    };

    $expect = static function (string $resp, array $codes): bool {
        foreach ($codes as $c) {
            if (preg_match('/^' . preg_quote((string) $c, '/') . '/m', $resp)) {
                return true;
            }
        }
        return false;
    };

    $debug['stage'] = 'greeting';
    $greeting = $read();
    if (!$expect($greeting, ['220'])) {
        $_SESSION['smtp_last_error'] = $debug + ['server' => $greeting];
        fclose($fp);
        return false;
    }

    $ehloHost = 'localhost';
    $debug['stage'] = 'ehlo';
    $write('EHLO ' . $ehloHost);
    $ehlo = $read();
    if (!$expect($ehlo, ['250'])) {
        $write('HELO ' . $ehloHost);
        $helo = $read();
        if (!$expect($helo, ['250'])) {
            $_SESSION['smtp_last_error'] = $debug + ['server' => $ehlo . "\n" . $helo];
            fclose($fp);
            return false;
        }
    }

    if ($enc === 'tls') {
        $debug['stage'] = 'starttls';
        $write('STARTTLS');
        $starttls = $read();
        if (!$expect($starttls, ['220'])) {
            $_SESSION['smtp_last_error'] = $debug + ['server' => $starttls];
            fclose($fp);
            return false;
        }
        $cryptoOk = @stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        if ($cryptoOk !== true) {
            $_SESSION['smtp_last_error'] = $debug + ['error' => 'TLS negotiation failed'];
            fclose($fp);
            return false;
        }
        $write('EHLO ' . $ehloHost);
        $ehlo2 = $read();
        if (!$expect($ehlo2, ['250'])) {
            $_SESSION['smtp_last_error'] = $debug + ['server' => $ehlo2];
            fclose($fp);
            return false;
        }
    }

    $debug['stage'] = 'auth_login';
    $write('AUTH LOGIN');
    $r1 = $read();
    if (!$expect($r1, ['334'])) {
        $_SESSION['smtp_last_error'] = $debug + ['server' => $r1];
        fclose($fp);
        return false;
    }
    $write(base64_encode($username));
    $r2 = $read();
    if (!$expect($r2, ['334'])) {
        $_SESSION['smtp_last_error'] = $debug + ['server' => $r2];
        fclose($fp);
        return false;
    }
    $write(base64_encode($password));
    $r3 = $read();
    if (!$expect($r3, ['235'])) {
        $_SESSION['smtp_last_error'] = $debug + ['server' => $r3];
        fclose($fp);
        return false;
    }

    $debug['stage'] = 'mail_from';
    $write('MAIL FROM:<' . $fromEmail . '>');
    $mailFromResp = $read();
    if (!$expect($mailFromResp, ['250'])) {
        $_SESSION['smtp_last_error'] = $debug + ['server' => $mailFromResp];
        fclose($fp);
        return false;
    }

    $debug['stage'] = 'rcpt_to';
    $write('RCPT TO:<' . $toEmail . '>');
    $rcpt = $read();
    if (!$expect($rcpt, ['250', '251'])) {
        $_SESSION['smtp_last_error'] = $debug + ['server' => $rcpt];
        fclose($fp);
        return false;
    }

    $debug['stage'] = 'data';
    $write('DATA');
    $dataIntro = $read();
    if (!$expect($dataIntro, ['354'])) {
        $_SESSION['smtp_last_error'] = $debug + ['server' => $dataIntro];
        fclose($fp);
        return false;
    }

    $replyToEmail = trim((string) ($cfg['reply_to_email'] ?? ''));
    $replyToName = trim((string) ($cfg['reply_to_name'] ?? ''));

    $headers = [];
    $headers[] = 'From: ' . ($fromName !== '' ? $fromName . ' ' : '') . '<' . $fromEmail . '>';
    if ($replyToEmail !== '' && filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
        $headers[] = 'Reply-To: ' . ($replyToName !== ''
            ? '=?UTF-8?B?' . base64_encode($replyToName) . '?= <' . $replyToEmail . '>'
            : '<' . $replyToEmail . '>');
    }
    $headers[] = 'To: <' . $toEmail . '>';
    $headers[] = 'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';

    $data = implode("\r\n", $headers) . "\r\n\r\n" . $body;
    $data = preg_replace("/\\r\\n\\./", "\r\n..", $data);

    fwrite($fp, $data . "\r\n.\r\n");
    $sent = $read();
    if (!$expect($sent, ['250'])) {
        $_SESSION['smtp_last_error'] = $debug + ['server' => $sent];
        fclose($fp);
        return false;
    }

    $write('QUIT');
    $read();
    fclose($fp);
    unset($_SESSION['smtp_last_error']);
    return true;
}

function app_send_plain_email(string $toEmail, string $subject, string $body, ?string $replyToEmail = null, ?string $replyToName = null): bool
{
    $fromEmail = defined('SMTP_FROM_EMAIL') ? (string) SMTP_FROM_EMAIL : 'no-reply@localhost';
    $fromName = defined('SMTP_FROM_NAME') ? (string) SMTP_FROM_NAME : 'Cosmetic';

    $replyToEmail = $replyToEmail !== null ? trim($replyToEmail) : '';
    $replyToName = $replyToName !== null ? trim($replyToName) : '';

    if (defined('SMTP_ENABLED') && SMTP_ENABLED) {
        $cfg = [
            'host' => (string) SMTP_HOST,
            'port' => (int) SMTP_PORT,
            'encryption' => (string) SMTP_ENCRYPTION,
            'username' => (string) SMTP_USERNAME,
            'password' => (string) SMTP_PASSWORD,
            'verify_peer' => defined('SMTP_VERIFY_PEER') ? (bool) SMTP_VERIFY_PEER : true,
            'allow_self_signed' => defined('SMTP_ALLOW_SELF_SIGNED') ? (bool) SMTP_ALLOW_SELF_SIGNED : false,
            'from_email' => $fromEmail,
            'from_name' => $fromName,
            'to_email' => $toEmail,
            'subject' => $subject,
            'body' => $body,
        ];
        if ($replyToEmail !== '' && filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
            $cfg['reply_to_email'] = $replyToEmail;
            $cfg['reply_to_name'] = $replyToName;
        }
        return smtp_send_mail($cfg);
    }

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . $fromName . ' <' . $fromEmail . '>',
    ];
    if ($replyToEmail !== '' && filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
        $headers[] = 'Reply-To: ' . ($replyToName !== ''
            ? '=?UTF-8?B?' . base64_encode($replyToName) . '?= <' . $replyToEmail . '>'
            : '<' . $replyToEmail . '>');
    }

    return @mail($toEmail, $subject, $body, implode("\r\n", $headers));
}
