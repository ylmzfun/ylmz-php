<?php

namespace Ylmz\Support;

use Ylmz\Foundation\Config;

class Mail
{
    private string $to = '';
    private string $subject = '';
    private string $body = '';
    private string $from = '';
    private string $fromName = '';
    private array $headers = [];

    public static function new(): self { return new self(); }

    public function to(string $address): self           { $this->to = $address; return $this; }
    public function subject(string $subject): self      { $this->subject = $subject; return $this; }
    public function body(string $body): self            { $this->body = $body; return $this; }
    public function from(string $address, string $name = ''): self { $this->from = $address; $this->fromName = $name; return $this; }
    public function html(string $html): self            { $this->headers[] = 'Content-Type: text/html; charset=UTF-8'; $this->body = $html; return $this; }
    public function cc(string $address): self           { $this->headers[] = "Cc: {$address}"; return $this; }
    public function bcc(string $address): self          { $this->headers[] = "Bcc: {$address}"; return $this; }
    public function replyTo(string $address): self      { $this->headers[] = "Reply-To: {$address}"; return $this; }
    public function attach(string $path, string $name = ''): self
    {
        $name = $name ?: basename($path);
        $content = chunk_split(base64_encode(file_get_contents($path)));
        $this->headers[] = "Content-Type: application/octet-stream; name=\"{$name}\"";
        $this->headers[] = "Content-Disposition: attachment; filename=\"{$name}\"";
        $this->headers[] = "Content-Transfer-Encoding: base64";
        $this->body .= "\n\n{$content}";
        return $this;
    }

    /**
     * Send via SMTP.
     */
    public function send(): bool
    {
        $from = $this->from ?: Config::get('MAIL_FROM', 'noreply@localhost');
        $fromName = $this->fromName ?: Config::get('MAIL_FROM_NAME', '');

        $headers = array_merge([
            'MIME-Version: 1.0',
            'From: ' . ($fromName ? "{$fromName} <{$from}>" : $from),
        ], $this->headers);

        $smtp = Config::get('MAIL_DRIVER', 'sendmail');

        if ($smtp === 'smtp') {
            return $this->sendSmtp($from, $headers);
        }

        return $this->sendSendmail($from, $headers);
    }

    private function sendSendmail(string $from, array $headers): bool
    {
        $headerString = implode("\r\n", $headers);
        return mail($this->to, $this->subject, $this->body, $headerString, "-f {$from}");
    }

    private function sendSmtp(string $from, array $headers): bool
    {
        $host = Config::get('MAIL_HOST', 'localhost');
        $port = Config::getInt('MAIL_PORT', 587);
        $user = Config::get('MAIL_USER', '');
        $pass = Config::get('MAIL_PASS', '');
        $encryption = Config::get('MAIL_ENCRYPTION', 'tls');

        $sock = @fsockopen(
            ($encryption === 'ssl' ? 'ssl://' : '') . $host,
            $port,
            $errno,
            $errstr,
            10
        );

        if (!$sock) {
            throw new \RuntimeException("SMTP connection failed: {$errstr}");
        }

        $this->smtpCommand($sock, null);
        $this->smtpCommand($sock, "EHLO {$host}");
        if ($encryption === 'tls') {
            $this->smtpCommand($sock, 'STARTTLS');
            stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->smtpCommand($sock, "EHLO {$host}");
        }

        if ($user) {
            $this->smtpCommand($sock, 'AUTH LOGIN');
            $this->smtpCommand($sock, base64_encode($user));
            $this->smtpCommand($sock, base64_encode($pass));
        }

        $this->smtpCommand($sock, "MAIL FROM:<{$from}>");
        $this->smtpCommand($sock, "RCPT TO:<{$this->to}>");
        $this->smtpCommand($sock, 'DATA');

        $headerString = implode("\r\n", $headers);
        $message = "Subject: {$this->subject}\r\n{$headerString}\r\n\r\n{$this->body}\r\n.";
        $this->smtpCommand($sock, $message);
        $this->smtpCommand($sock, 'QUIT');

        fclose($sock);
        Event::dispatch('mail.sent', ['to' => $this->to, 'subject' => $this->subject]);

        return true;
    }

    private function smtpCommand($sock, ?string $cmd): void
    {
        if ($cmd !== null) {
            fwrite($sock, $cmd . "\r\n");
        }
        $response = fgets($sock, 512);
        if ($response === false) return;
        $code = (int)substr($response, 0, 3);
        if ($code >= 400) {
            fclose($sock);
            throw new \RuntimeException("SMTP error: {$response}");
        }
    }
}
