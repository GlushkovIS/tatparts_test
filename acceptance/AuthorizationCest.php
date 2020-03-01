<?php
/**
 * Created by PhpStorm.
 * User: a.butorina
 * Date: 11.06.2019
 * Time: 12:06
 */

use Pages\AuthClientPage;

/**
 * Данный класс проверяет успешную авторизацию
 *
 * @group smoke
 * @group AuthorizationCest
 * Class AuthorizationCest
 */
class AuthorizationCest
{
    public function testClientAuthFailed(AcceptanceTester $I)
    {
        $I->wantTo('Проверить неуспешную авторизацию тестового клиента');

        $loginDataFailed = [
            'login' => 'testtme',
            'password' => '1123'
        ];

        $AuthClientPage = new AuthClientPage($I);

        $I->amGoingTo('Переход на главную страницу сайта');
        $AuthClientPage->goPage();

        $I->amGoingTo('Авторизация с несуществующими данными');
        $AuthClientPage->fillAuthData($loginDataFailed);
        $AuthClientPage->clickLogin();

        $I->amGoingTo('Проверка, что авторизация не успешна');
        $AuthClientPage->checkLoginFailed();
    }

    public function testClientAuthSuccess(AcceptanceTester $I)
    {
        $I->wantTo('Проверить успешную авторизацию тестового клиента');

        $loginDataSuccess = [
            'login' => 'testme',
            'password' => '123'
        ];

        $AuthClientPage = new AuthClientPage($I);

        $I->amGoingTo('Переход на главную страницу сайта');
        $AuthClientPage->goPage();

        $I->amGoingTo('Авторизация с верными данными');
        $AuthClientPage->fillAuthData($loginDataSuccess);
        $AuthClientPage->clickLogin();

        $I->amGoingTo('Проверка, что авторизация успешна');
        $AuthClientPage->checkLoginSuccess();

        $I->amGoingTo('Выйти из профиля');
        $AuthClientPage->logout();
    }
}