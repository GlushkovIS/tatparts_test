<?php

namespace Pages\Admin;

use Pages\AdminPage;

/**
 * Веб-Авторесурс ► Интернет-магазин ► Заказы ► Список заказов
 *
 * Class OrdersAdminPage
 * @package Pages
 */
class OrdersAdminPage extends AdminPage
{
    const LINK_PAGE = '/admin/eshop/orders/list.html';
    const BTN_DEL = '//tr[3]//td[@class="col_delete"]//a';
    const LINK_DEL = '//a[@rel="force_delete"]';
    const NOTICE_DELETE = '//div[@class="notice"]';

    protected static $checkElement = '//table[@class="admin_edit_table"]';

    /**
     * Удаляет последний заказ клиента и связанные документы
     */
    public function deleteLastOrder()
    {
        $I = $this->user;
        $I->click(self::BTN_DEL);
        $I->acceptPopup();
        $I->click(self::LINK_DEL);
        $I->acceptPopup();
        $I->seeElement(self::NOTICE_DELETE);
    }

}
