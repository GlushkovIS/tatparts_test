<?php

namespace Pages;

/**
 * базовый класс для всех страниц административной части
 *
 * Class AdminPage
 * @package Pages
 */
class AdminPage extends BasePage
{
    /** Логин-пароль админа */
    const ADMIN_LOGIN = 'test';
    const ADMIN_PASS = '123';

    /** Ссылка  админки */
    const LINK_PAGE = '/admin/navigation.html';
    
    /** Блок авторизации */
    const INFO_PATH = '#bigPanel #logout';
    const LOGOUT_PATH = '//*[contains(@onclick, "logout")]';

    const BTN_FILTER_APPLY = 'input[name=apply][type="submit"]';

    const MSG_NOTICE = '//*[contains(@class,"notice")]';
    const GO_BACK = self::MSG_NOTICE . '/a[contains(@href, "/admin/")]';

    // Url для авторизации админки
    public static $admURL;

    /**
     * Авторизация админа
     * по умолчанию используются дефолтный логин и пароль админа
     *
     * @param string $login
     * @param string $password
     * @throws \Codeception\Exception\ModuleException
     */
    public function authAdmin($login = self::ADMIN_LOGIN, $password = self::ADMIN_PASS)
    {
        $I = $this->user;
        self::$admURL = str_replace('://', '://' . $login . ':' . $password . '@', $I->getBaseUrl()) . self::LINK_PAGE;

        $I->amOnUrl(self::$admURL);
        $I->waitForElement(self::INFO_PATH);
        $I->expect('Авторизованный доступ в админку получен');
    }

    /**
     * Применить фильтр
     */
    public function applyFilter()
    {
        $I = $this->user;
        $I->amGoingTo('Применить фильтр');
        $I->waitForElementVisible(static::BTN_FILTER_APPLY);
        $I->click(static::BTN_FILTER_APPLY);
    }
}
