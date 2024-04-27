<?php

if (isset($_POST['submit'])) {
    $vorname = $_POST["vorname"];
    $nachname = $_POST["nachname"];
    $email = $_POST["email"];
    $subject = $_POST["subject"];
    $message = $_POST["msg"];


    $to = "emil@maple-ink.com";
    $subject = "[Kontaktformular] {$subject}";
    $body = "Name: {$vorname} {$nachname}\nEmail: {$email}\Nachricht:\n{$message}";
    $headers = "From: {$email}";


    mail($to, $subject, $body, $headers);

    header("Location: /kontakt");
}