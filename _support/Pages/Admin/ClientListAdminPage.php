<?php
/**
 * Created by PhpStorm.
 * User: t.chervyakova
 * Date: 12.12.2018
 * Time: 16:41
 */

namespace Pages\Admin;

/**
 * Веб-Авторесурс ► Интернет-магазин ► Клиенты ► Список клиентов
 *
 * Class ClientListAdminPage
 * @package Pages
 */
class ClientListAdminPage extends \Pages\AdminPage
{
    const LINK_PAGE = '/admin/eshop/clients/list.html';
    const TITLE_PAGE = 'Список клиентов';

    protected static $checkElement = '//table[@data-id="customers"]';

    /**
     * Проверяет наличие в списке клиента по логину
     *
     * @param $login
     * @throws \Exception
     */
    public function seeClientByLogin($login)
    {
        $this->user->waitForElementVisible('//td[@class="col_userlogin" and contains(string(), "' . $login . '")]');
    }

    public function dontSeeClientByLogin($login)
    {
        $this->user->dontSee('//td[@class="col_userlogin" and contains(string(), "' . $login . '")]');
    }

    public function setFilterByLogin($login)
    {
        $this->user->fillField('[name=flt_userlogin]', $login);
        $this->applyFilter();
    }

    public function deleteClientByLogin($login)
    {
        $I = $this->user;
        $I->waitForElementVisible('//td[@class="col_userlogin" and contains(string(), "' . $login . '")]');
        $I->click('//td[@class="col_userlogin" and contains(string(), "' . $login . '")]/ancestor::tr//td[@class="col_deletelink"]/a');
        $I->acceptPopup();
        $I->waitForElementVisible(static::NOTICE_DELETE);
        $I->waitForElementVisible(static::GO_BACK);
        $I->click(static::GO_BACK);
    }
}
