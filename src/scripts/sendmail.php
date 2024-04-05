<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(
        !empty($_POST['name'])
        && !empty($_POST['email'])
        && !empty($_POST['message'])
    ){
        $vorname = $_POST["vorname"];
        $nachname = $_POST["nachname"];
        $email = $_POST["email"];
        $subject = $_POST["subject"];
        $message = $_POST["msg"];


        $to = "emil@maple-ink.com";
        $subject = "[Kontaktformular] ${subject}";
        $body = "Name: {$vorname} {$nachname}\nEmail: {$email}\Nachricht:\n{$message}";
        $headers = "From: {$email}";


        if (mail($to, $subject, $body, $headers)) {
            echo "Message sent successfully!";
        } else {
            echo "Failed to send message.";
        }
    }
}