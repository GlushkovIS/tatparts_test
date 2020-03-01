<?php

namespace Pages\Client;

/**
 * Страница Регистрации клиента
 *
 * Class RegistrationClientPage
 * @package Pages
 */
class RegistrationClientPage extends \Pages\ClientPage
{
    const LINK_PAGE = '/registration.html?not_use_captcha=Y';

    const MSG_REGISTER_COMPLETE = ".//div[@id='registration_div' and contains(string(),'Регистрация завершена')]";
    const MSG_REGISTER_SUCCESS = ".//div[@id='registration_div' and contains(string(),'Вы успешно зарегистрировались в нашем магазине.')]";

    const BTN_REGISTER = '[name=register]';

    const FLD_REGION = '//div[@id="add_region_id_ds"]';
    const FLD_CITY = '//div[@id="add_city_id_ds"]';
    const FLD_FIRSTNAME = '#contact_first_name';
    const FLD_SURNAME = '#contact_surname';
    const FLD_PATRONYMIC = "//*[@name = 'contact_patronymic_name']";
    const FLD_PHONE = '#contact_phone';
    const FLD_EMAIL = '#cst_email';
    const FLD_STOCK = '//div[@id="stc_id_ds"]';
    const FLD_ADDRESS = '#ord_address';
    const FLD_LOGIN = '#tr_userlogin #userlogin';
    const FLD_PASSWORD = '#tr_userpassword #userpassword';
    const FLD_PASSWORD_REPEAT = '#userpassword_repeat';
    const FLD_CAPTURE = '#reg_hc_code';
    const FLD_OFERTA = '.oferta_reg .custom_checkbox';

    public function getCheckElement()
    {
        return static::$checkElement = static::BTN_REGISTER;
    }

    public function goPage()
    {
        $this->logoutClient();
        $this->goPageParams($this->getCheckElement(), static::LINK_PAGE);
    }

    public function saveRegistration()
    {
        $I = $this->user;
        $I->click(static::BTN_REGISTER);
    }

    public function checkRegSuccess()
    {
        $I = $this->user;
        $I->waitForElement(static::MSG_REGISTER_COMPLETE);
        $I->seeElement(static::MSG_REGISTER_SUCCESS);
    }

    public function checkRegNotSuccess()
    {
        $I = $this->user;
        $I->dontSeeElement(static::MSG_REGISTER_COMPLETE);
        $I->dontSeeElement(static::MSG_REGISTER_SUCCESS);
    }

    /**
     * Заполнение полей
     *
     * @param $clientData
     * @throws \Exception
     */
    public function fillRecord($clientData)
    {
        $I = $this->user;

        $I->setSelectInClient(self::FLD_REGION, $clientData['region']);
        $I->setSelectInClient(self::FLD_CITY, $clientData['city']);

        $I->fillField(self::FLD_SURNAME, $clientData['surname']);
        $I->fillField(self::FLD_FIRSTNAME, $clientData['name']);

        $I->fillField(self::FLD_PHONE, $clientData['phone']);
        $I->fillField(self::FLD_EMAIL, $clientData['mail']);

        $I->setSelectInClient(self::FLD_STOCK, $clientData['stock']);

        $I->fillField(self::FLD_ADDRESS, $clientData['address']);
        $I->fillField(self::FLD_LOGIN, $clientData['login']);
        $I->fillField(self::FLD_PASSWORD, $clientData['password']);
        $I->fillField(self::FLD_PASSWORD_REPEAT, $clientData['repeatPassword']);
        $I->fillField(self::FLD_CAPTURE, $clientData['captcha']);

        $I->click(self::FLD_OFERTA);
    }
}
