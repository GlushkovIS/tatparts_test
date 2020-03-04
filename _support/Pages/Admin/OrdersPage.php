<?php

namespace Pages\Admin;

use Pages\AdminPage;

/**
 * Веб-Авторесурс ► Интернет-магазин ► Заказы ► Список заказов
 *
 * Class OrdersPage
 * @package Pages
 */
class OrdersPage extends AdminPage
{
    const LINK_PAGE = '/admin/eshop/orders/list.html';
    const TITLE_PAGE = 'Список заказов';
    const BTN_DEL = '//tr[3]//td[@class="col_delete"]//a';
    const LINK_DEL = '//div[@class="error" and contains(string(), "Для удаления позиции")]//a';

    protected static $checkElement = '//table[@class="admin_edit_table"]';

    /**
     * Удаляет последний заказ
     */
    public function deleteLastOrder()
    {
        $I = $this->user;
        $I->click(self::BTN_DEL);
        $I->acceptPopup();
        $I->click(self::LINK_DEL);
        $I->acceptPopup();
    }

}
