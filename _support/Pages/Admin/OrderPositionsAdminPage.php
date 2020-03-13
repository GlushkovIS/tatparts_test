<?php

namespace Pages\Admin;

use Pages\AdminPage;

/**
 * Веб-Авторесурс ► Интернет-магазин ► Заказы ► Позиции заказов
 *
 * Class OrderPositionsAdminPage
 * @package Pages
 */
class OrderPositionsAdminPage extends AdminPage
{
    const LINK_PAGE = '/admin/eshop/orders/positions_list.html';

    /** Поля таблицы позиций заказа клиента */
    const TD_PRICE = '//td[@class="col_pst_price"]';
    const TD_ARTICLE = '//td[contains(@class,"col_pst_article")]';
    const TD_NAME = '//td[contains(@class, "col_pst_name")]';
    const TD_BRAND = '//td[contains(@class, "col_pst_brand")]';
    const TD_CLIENT_DIRECTION = '//td[contains(@class,"col_pst_direction")]';
    const TD_DIRECTION = '//td[contains(@class,"col_pst_destination")]';
    const TD_SUPPLIER = '//td[@class="col_short_name"]';
    const BTN_EDIT = '//td[contains(@class,"col_edit")]//a';

    /** Поля формы редактирования позиции ЗК*/
    const ADMIN_EDIT_TABLE = '//table[@id="admin-edit-table"]';
    const DIRECTION = '//input[@id="pst_destination"]';
    const BTN_SEND = '//input[@name="send"]';

    protected static $checkElement = '//table[@id="table-positions"]';

    /**
     * Проверяет направление в колонке "направление которое видит клиент"
     * @param array $positionData
     */
    public function checkDisplayClientDirection(array $positionData, $direction)
    {
        $I = $this->user;
        $path = $this->getTableRow($positionData) . self::TD_CLIENT_DIRECTION;
        $I->see($direction, $path);
    }

    /**
     * Проверяет соотвествует ли направление в колонке "направление" переданному
     * @param array $positionData
     * @param $direction
     */
    public function checkDisplaySupplierDirection(array $positionData, $direction)
    {
        $I = $this->user;
        $path = $this->getTableRow($positionData) . self::TD_DIRECTION . '//input';
        $I->seeInField($path, $direction);
    }

    /**
     * Возвращает локатор строки позиции заказа
     * @param array $positionData
     * @return string
     */
    public function getTableRow(array $positionData)
    {
        $name = '';
        if (!empty($positionData['name'])) {
            $name = ' and .' . self::TD_NAME . '//input[contains(@value,"' . $positionData['name'] . '")]';
        }

        $row = '//tr[.' . self::TD_ARTICLE . '[contains(text(),"' . $positionData['article'] . '")][1] and .'
            . self::TD_BRAND . '//input[contains(@value,"' . $positionData['brand'] . '")]' . $name . ']';
        return $row;
    }

    /**
     * Меняет направление позиции
     * @param string $newDirection
     * @throws \Exception
     */
    public function changeDirection(array $positionData, $newDirection)
    {
        $I = $this->user;
        $path = $this->getTableRow($positionData) . self::BTN_EDIT;
        $I->click($path);
        $I->waitForElementVisible(self::ADMIN_EDIT_TABLE);
        $I->fillField(self::DIRECTION, $newDirection);
        $I->clickWithLeftButton(self::BTN_SEND);
    }
}
