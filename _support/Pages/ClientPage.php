<?php
/**
 * Created by PhpStorm.
 * User: t.chervyakova
 * Date: 07.05.18
 * Time: 9:51
 */

namespace Pages;

/**
 * базовый класс для всех страниц клиентской части
 * тут должны быть методы одинаковые для ВСЕХ страниц клиентки, например авторизация
 *
 * Class ClientPage
 * @package WarCodecept\Tests\Support\Pages\Client
 */
class ClientPage extends BasePage
{
    /**
     * Логин-пароль клиента
     */
    const CLIENT_LOGIN = 'testme';
    const CLIENT_PASS = '123';

    /** Блок авторизации */
    const USER_DROP_MENU = '//*[contains(@class, "auth-user")]//*[contains(@class, "user-menu")]';
    const AUTH_USER_BLOCK = '.b-user-links .b-user-name';
    const NOT_AUTH_USER_BLOCK = '.b-auth-form';

    /** Url для авторизации клиентки */
    public static $clientURL;

    /**
     * Авторизация клиента. По умолчанию используются дефолтный логин и пароль клиента
     *
     * @param $login
     * @param $password
     * @throws \Exception
     */
    public function authClient($login = self::CLIENT_LOGIN, $password = self::CLIENT_PASS)
    {
        $I = $this->user;
        self::$clientURL = $I->getBaseUrl() . '?userlogin=' . $login . '&userpassword=' . $password . '&loginform=1';

        $I->amOnUrl(self::$clientURL);
        $I->waitForElement(static::AUTH_USER_BLOCK);
    }

    /**
     * Проверить, что авторизованный доступ в кл.часть получен
     */
    public function checkClientAuthSuccess()
    {
        $I = $this->user;
        $I->waitForElementVisible(static::AUTH_USER_BLOCK);
    }

    /**
     * Логаут клиента
     */
    public function logoutClient()
    {
        $I = $this->user;
        self::$clientURL = $I->getBaseUrl() . '?logout';

        $I->amOnUrl(self::$clientURL);
        $I->waitForElement(static::NOT_AUTH_USER_BLOCK);
    }

    /**
     * Проверить, что авторизованный доступ в кл.часть не получен
     */
    public function checkClientAuthFailed()
    {
        $I = $this->user;
        $I->waitForElementVisible(static::NOT_AUTH_USER_BLOCK);
    }

    /**
     * Метод для сброса сессии.
     * Если заходили в адм.часть, чтобы обеспечить безошибочную работу в клиент.части
     */
    public function resetLoginClient()
    {
        $this->authClient();
        $this->logoutClient();
    }

    /**
     * Убирает капчу из адресной строки
     */
    public function disableCaptcha()
    {
        $I = $this->user;
        $curUrl = $I->getCurrentUrl();
        $urlArr = explode('?', $curUrl);
        $urlDisCaptcha = $urlArr[0] . '?not_use_captcha=Y';

        for ($i = 1; $i < count($urlArr); $i++) {
            $urlDisCaptcha = $urlDisCaptcha . '&' . $urlArr[$i];
        }
        $I->amOnUrl($I->getBaseUrl() . $urlDisCaptcha);
    }
}
