# PHP `echo` Command

The `echo` command in PHP is used to output one or more strings. It is a language construct, not a function, so you can use it without parentheses. The output is sent to the standard output, which is typically the web browser when running PHP scripts on a web server.

## How It Works

When you use the `echo` command in a PHP script, the specified string or strings are sent to the web server's response, which is then rendered by the web browser.

### Example

In the provided PHP script:

```php
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ...existing code...
    if (mail($to, $subject, $message, $headers)) {
        echo "Email sent successfully.";
    } else {
        echo "Failed to send email.";
    }
}
?>
```

The `echo` command outputs the result of the email sending process. If the email is sent successfully, it outputs "Email sent successfully." Otherwise, it outputs "Failed to send email."

## Viewing the Output

To view the output of the `echo` command:

1. Open the PHP script in a web browser.
2. Perform the action that triggers the PHP code (e.g., submitting a form).
3. The output will be displayed in the web browser where the PHP script is rendered.

In the provided example, after submitting the form, the message "Email sent successfully." or "Failed to send email." will be displayed on the web page.
