<?php

namespace Pages\Client;

/**
 * Клиентская часть. Личный кабинет ► Мои заказы ► Позиции заказов
 *
 * Class MyOrdersClientPage
 * @package Pages\Client
 */
class MyOrdersClientPage extends \Pages\ClientPage
{
    const LINK_PAGE = '/shop/myorders.html';

    /** Поля таблицы позиций заказа клиента */
    const TD_PRICE = '//td[@class="col_pst_price"]';
    const TD_ARTICLE = '//td[contains(@class,"col_pst_article")]';
    const TD_BRAND_NAME = '//td[contains(@class, "col_pst_brand")]';
    const TD_DESTINATION = '//td[@class="col_pst_destination_display"]';

    /**
     * Проверяет соотвествует ли направление ожидаемому
     * @param array $positionData
     */
    public function checkDisplayDirection(array $positionData, $direction)
    {
        $I = $this->user;
        $path = $this->getTableRow($positionData) . self::TD_DESTINATION;
        $I->see($direction, $path);
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
            $name = ' and .' . self::TD_BRAND_NAME . '[contains(string(), "' . $positionData['nameAndBrand'] . '")]';
        }

        $row = '//tr[.' . self::TD_ARTICLE . '[normalize-space()="' . $positionData['article'] . '"] and .'
            . self::TD_BRAND_NAME . '[normalize-space()="' . $positionData['nameAndBrand'] . '"]' . $name . ']';
        return $row;
    }
}
