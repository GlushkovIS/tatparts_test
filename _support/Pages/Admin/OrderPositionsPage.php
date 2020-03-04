<?php

namespace Pages\Admin;

use Pages\AdminPage;

/**
 * Веб-Авторесурс ► Интернет-магазин ► Заказы ► Позиции заказов
 *
 * Class OrderPositionsPage
 * @package Pages
 */
class OrderPositionsPage extends AdminPage
{
    const LINK_PAGE = '/admin/eshop/orders/positions_list.html';
    const TITLE_PAGE = 'Список позиций';
    /** Поля таблицы позиций заказа клиента */
    const TD_PRICE = '//td[@class="col_pst_price"]';
    const TD_ARTICLE = '//td[contains(@class,"col_pst_article")]';
    const TD_NAME = '//td[contains(@class, "col_pst_name")]';
    const TD_BRAND = '//td[contains(@class, "col_pst_brand")]';
    const TD_CLIENT_DESTINATION = '//td[contains(@class,\'col_pst_direction\')]';
    const TD_DESTINATION = '//td[contains(@class,\'col_pst_destination\')]';
    const TD_SUPPLIER = '//td[@class="col_short_name"]';
    const BTN_EDIT = '//tr[@id=\'0\']//td[contains(@class,\'col_edit\')]//a';
    /** Поля формы редактирования позиции ЗК*/
    const ADMIN_EDIT_TABLE = '//table[@id=\'admin-edit-table\']';
    const DIRECTION = '//input[@id=\'pst_destination\']';
    const BTN_SEND = '//input[@name=\'send\']';
    protected static $checkElement = '//table[@id="table-positions"]';

    /**
     * Проверяет направление в колонке "направление которое видит клиент"
     * @param array $searchRow
     */
    public function checkDisplayClientDirection(array $searchRow)
    {
        $I = $this->user;
        $path = $this->getTableRow($searchRow) . self::TD_CLIENT_DESTINATION;
        $I->see($searchRow['direction'], $path);
    }

    /**
     * Возвращает локатор строки поиска
     * @param array $searchRow
     * @return string
     */
    public function getTableRow(array $searchRow)
    {
        $name = '';
        if (!empty($searchRow['name'])) {
            $name = ' and .' . self::TD_NAME . '//input[contains(@class,\'TextBox\')][contains(@value,\'' . $searchRow['name'] . '\')]';
        }

        $row = '//tr[@id=\'0\'][.' . self::TD_ARTICLE . '[contains(text(),\'' . $searchRow['article'] . '\')] and .'
            . self::TD_BRAND . '//input[contains(@class,\'TextBox\')][contains(@value,\'' . $searchRow['brand'] . '\')]' . $name . ']';
        return $row;
    }

    /** Меняет направление позиции
     * @param array $searchRow
     * @throws \Exception
     */
    public function changeDirection(array $searchRow)
    {
        $I = $this->user;
        $I->click(self::BTN_EDIT);
        $I->waitForElementVisible(self::ADMIN_EDIT_TABLE);
        $I->fillField(self::DIRECTION, $searchRow['newDirection']);
        $I->clickWithLeftButton(self::BTN_SEND);
    }

    /**
     * Проверяет направление в колонке "направление"
     * @param array $searchRow
     * @param $direction
     */
    public function checkDisplayRealDirection(array $searchRow, $direction)
    {
        $I = $this->user;
        $path = $this->getTableRow($searchRow) . self::TD_DESTINATION . '//input';
        $I->seeInField($path, $direction);
    }
}
