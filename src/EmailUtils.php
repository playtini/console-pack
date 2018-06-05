<?php

namespace Playtini\ConsolePack;

class EmailUtils
{
    public static function canonize($email)
    {
        $email = trim(trim($email), ' "');
        $email = mb_strtolower($email);
        $email = preg_replace('#[^a-z0-9@\.\-_]+#', '', $email);

        return $email;
    }

    public static function extractDomain($email)
    {
        if (!preg_match('#@(.+)$#', $email, $m)) {
            return null;
        }

        return $m[1];
    }

    public static function sanitizeDomain($domain)
    {
        $domain = mb_strtolower($domain);
        //$domain = str_replace(['/', '\\', '?', '*', ':', ';'], '', $domain);
        $domain = preg_replace('#[^a-z0-9\.\-_]+#', '', $domain);
        $domain = preg_replace('#\.\.+#', '.', $domain);
        $domain = preg_replace('#^con[^a-z]#', '-$0', $domain);

        return $domain;
    }

    public static function getTopDomains()
    {
        return [
            'mail.ru',
            'gmail.com',
            'yandex.ru',
            'bk.ru',
            'inbox.ru',
            'list.ru',
            'rambler.ru',
            'icloud.com',
            'gmail.ru',
            'mail.com',
            'ya.ru',
            'email.ru',
            'yandex.com',
            'email.com',
            'yahoo.com',
            'outlook.com',
            'hotmail.com',
            'yandex.ua',
            'sibmail.com',
            'qmail.com',
        ];
    }
}
