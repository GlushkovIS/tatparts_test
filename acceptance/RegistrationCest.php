<?php

/**
 * Class RegistrationCest
 *
 * @group smoke
 * @group RegistrationCest
 */
class RegistrationCest
{
    /** @var \Pages\Client\RegistrationClientPage */
    protected $RegistrationPage;
    /** @var \Pages\Admin\ClientListAdminPage */
    protected $ClientListPage;

    protected $clientData;

    public function _before(AcceptanceTester $I)
    {
        $this->RegistrationPage = new \Pages\Client\RegistrationClientPage($I);
        $this->ClientListPage = new \Pages\Admin\ClientListAdminPage($I);

        $this->clientData = [
            'region' => 'Байконур',
            'city' => 'Байконур',
            'surname' => 'surnameRegClient',
            'name' => 'nameRegClient',
            'phone' => '+7(111)111-11-11',
            'mail' => 'testRegClient@tradesoft.ru',
            'stock' => 'ТАТПАРТС головной',
            'address' => 'ул. Ленина, 61',
            'login' => 'loginReg',
            'password' => '123',
            'repeatPassword' => '123',
            'captcha' => '654',
        ];
    }

    /**
     * Перейти на страницу Регистрации
     * Заполнить поля
     * Нажать кнопку отправки запроса
     * Проверить блок авторизации клиента
     *
     * Авторизоваться в админке
     * Перейти на страницу список клиентов
     * Фильтр по логину
     * Проверка, что запись отображается в списке
     *
     * Проверить в папке _debug наличие файла примерно вида mail_{$email}
     *
     * Логин-логаут клиентки (чтобы не упали дальнейшие тесты)
     */
    public function testRegistration(AcceptanceTester $I)
    {
        $I->wantTo('Проверить регистрацию клиента');

        $I->amGoingTo('Заполнить поля данными клиента и сохранить запрос');

        $this->RegistrationPage->resetLoginClient();
        $this->RegistrationPage->goPage();
        $this->RegistrationPage->fillRecord($this->clientData);
        $dateTime =  $this->RegistrationPage->getDateTime();
        $this->RegistrationPage->saveRegistration();
        $this->RegistrationPage->checkRegSuccess();

        $I->amGoingTo('Проверить авторизованный доступ зарегистрированного клиента');
        $this->RegistrationPage->checkClientAuthSuccess();

        $I->amGoingTo('Проверить наличие клиента в списке клиентов в админке');
        $this->ClientListPage->authAdmin();
        $this->ClientListPage->goPage();

        $I->amGoingTo('Отфильтровать список клиентов по логину');
        $this->ClientListPage->setFilterByLogin($this->clientData['login']);

        $I->expect('В таблице есть клиент с данным логином');
        $this->ClientListPage->seeClientByLogin($this->clientData['login']);

        $I->amGoingTo('Проверить в папке _debug наличие письма');
        $I->assertTrue(
            $this->RegistrationPage->checkMail($this->clientData['mail'], $dateTime),
            'Письмо клиенту НЕ отправлено'
        );

        $I->amGoingTo('Предотвратить ошибки в следующих тестах');
        $this->RegistrationPage->resetLoginClient();
    }

    public function clearTestData(AcceptanceTester $I)
    {
        $I->wantTo('Очистить тестовые данные');

        $I->amGoingTo('Удалить тестового клиента');
        $this->ClientListPage->authAdmin();
        $this->ClientListPage->goPage();
        $this->ClientListPage->deleteClientByLogin($this->clientData['login']);
    }

}
