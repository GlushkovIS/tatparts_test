<?php

namespace Pages\Client;

/**
 * Клиентская часть. Главная ► Личный кабинет ► Корзина заказов
 *
 * Class BasketClientPage
 * @package Pages\Client
 */
class BasketClientPage extends \Pages\ClientPage
{
    const LINK_PAGE = '/shop/basket.html';
    const LOCAL_TABLE = "//*[@class='web_ar_datagrid basket']";
    const MSG_BASKET_EMPTY = "//form[@id='basket'][contains(string(), 'На данный момент Вы не добавили ни один товар в корзину')]";

    /** Кнопка Сделать заказ */
    const BTN_MAKE_ORDER = "//*[@name='save_order']";

    /** Отменить весь заказ, очистить корзину*/
    const BTN_CLEAR_BASKET = '.ar_cancelLink';

    const CHB_ALL_POSITIONS = '.col_chPos label';
    const CHB_OFERTA = '.oferta_basket label';

    const TD_SUMM = '//td[@class="col_summ"]';
    const TD_ARTICLE = '//td[@class="col_article"]';
    const TD_BRAND = '//td[@class="col_brand"]';
    const TD_NAME = '//td[@class="col_name"]';

    public function getCheckElement()
    {
        return static::$checkElement = self::LOCAL_TABLE . '|' . self::MSG_BASKET_EMPTY;
    }

    /**
     * Действие "Очистить корзину"
     *
     * @throws \Codeception\Exception\ModuleException
     */
    public function cleanBasket()
    {
        $I = $this->user;
        if ($I->seeElementExistInDom(self::BTN_CLEAR_BASKET)) {
            $I->click(self::BTN_CLEAR_BASKET);
            $I->acceptPopup();
        }
        $I->waitForElementVisible(self::MSG_BASKET_EMPTY);
    }

    /**
     * Выбирает все позиции в корзине
     */
    public function selectAllPositions()
    {
        $this->user->click(self::CHB_ALL_POSITIONS);
    }

    /**
     * Отмечает чекбокс "согласен с условиями оферты", если он есть
     * @throws \Codeception\Exception\ModuleException
     */
    public function acceptOferta()
    {
        $I = $this->user;
        if ($I->seeElementExistInDom(self::CHB_OFERTA)) {
            $I->click(self::CHB_OFERTA);
        }
    }

    /**
     * Оформить заказ
     */
    public function makeOrder()
    {
        $I = $this->user;
        $I->waitForElement(static::BTN_MAKE_ORDER);
        $I->click(static::BTN_MAKE_ORDER);
    }

    /**
     * Возвращает значение, находящееся в поле цены
     *
     * @param array $searchRow
     * @return mixed
     */
    public function getPositionPrice(array $searchRow)
    {
        $path = '//tr[.' . self::TD_ARTICLE . '[contains(text(), "' . $searchRow['article'] . '")] and .'
            . self::TD_BRAND . '[contains(text(), "' . $searchRow['brand'] . '")] and .'
            . self::TD_NAME . '[./*[@value="' . $searchRow['name'] . '"]]]' . self::TD_SUMM;
        return $this->user->grabTextFrom($path);
    }


}
