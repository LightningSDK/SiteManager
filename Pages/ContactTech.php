<?php

namespace lightningsdk\sitemanager\Pages;

use lightningsdk\core\Tools\Configuration;
use lightningsdk\core\Tools\Mailer;

class ContactTech extends Contact {

    public function sendMessage() {
        $mailer = new Mailer();
        foreach (Configuration::get('sitemanager.contacts') as $contact) {
            $mailer->to($contact);
        }
        return $mailer
            ->replyTo($this->user->email)
            ->subject($this->settings['subject'])
            ->message($this->getMessageBody())
            ->send();
    }
}
