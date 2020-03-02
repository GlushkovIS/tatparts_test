<?php

namespace Pages\Client;

/**
 * Клиентская часть. Личный кабинет ► Мои заказы ► Позиции заказов
 *
 * Class MyOrdersPage
 * @package Pages\Client
 */
class MyOrdersPage extends \Pages\ClientPage
{
    const LINK_PAGE = '/shop/myorders.html';

    /** Поля таблицы позиций заказа клиента */
    const TD_PRICE = '//td[@class="col_pst_price"]';
    const TD_ARTICLE = '//td[contains(@class,"col_pst_article")]';
    const TD_BRAND_NAME = '//td[contains(@class, "col_pst_brand")]';
    const TD_DESTINATION = '//td[@class="col_pst_destination_display"]';


    public function checkDisplayDirection(array $searchRow)
    {
        $I = $this->user;
        $path = $this->getTableRow($searchRow) . self::TD_DESTINATION;
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
            $name = ' and .' . self::TD_BRAND_NAME . '[contains(string(), "' . $searchRow['nameAndBrand'] . '")]';
        }

        $row = '//tr[.' . self::TD_ARTICLE . '[normalize-space()="' . $searchRow['article'] . '"] and .'
            . self::TD_BRAND_NAME . '[normalize-space()="' . $searchRow['nameAndBrand'] . '"]' . $name . ']';
        return $row;
    }
}
