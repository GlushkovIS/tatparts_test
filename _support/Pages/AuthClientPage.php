<?php
/**
 * Created by PhpStorm.
 * User: t.chervyakova
 * Date: 05.11.2019
 * Time: 9:37
 */

namespace Pages;

class AuthClientPage extends BasePage
{
    const FLD_LOGIN = '[name="userlogin"]';
    const FLD_PASSWORD = '[name="userpassword"]';
    const BLOCK_AUTH = 'form#login';
    const AUTH_FAILED = '.login_error';
    const BTN_HELLO_ON_PANEL = '.b-user-links';
    const BTN_LOGIN = '.loginButton';
    const BTN_LOGOUT = '.b-logout__link';

    public function getCheckElement()
    {
        return static::$checkElement = static::BLOCK_AUTH;
    }

    public function fillAuthData($authData)
    {
        $I = $this->user;
        $I->fillfield(self::FLD_LOGIN, $authData['login']);
        $I->fillfield(self::FLD_PASSWORD, $authData['password']);
    }

    public function clickLogin()
    {
        $I = $this->user;
        $I->click(static::BTN_LOGIN);
    }

    public function checkLoginSuccess()
    {
        $I = $this->user;
        $I->waitForElementVisible(static::BTN_HELLO_ON_PANEL);
    }

    public function checkLoginFailed()
{
    $I = $this->user;
    $I->waitForElementVisible(static::AUTH_FAILED);
}

    public function logout()
    {
        $I = $this->user;
        $I->click(static::BTN_LOGOUT);
        $I->waitForElementNotVisible(static::BTN_HELLO_ON_PANEL);
    }
}
