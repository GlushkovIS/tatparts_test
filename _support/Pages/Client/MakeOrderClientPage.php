<?php

namespace Pages\Client;

/**
 * Клиентская часть. Главная ► Оформление заказа
 *
 * Class MakeOrderClientPage
 * @package Pages\Client
 */
class MakeOrderClientPage extends \Pages\ClientPage
{
    const LINK_PAGE = '/shop/make_order.html?disable_captcha=Y';
    const LOCAL_TABLE = '//table[@class="web_ar_datagrid make_order"]';

    const FLD_PAYMENT = '//div[@id="ord_pmk_id_ds"]';

    /** Сделать заказ */
    const BTN_SAVE_ORDER = '//input[@name="save_order"]';

    /** Заказ успешен */
    const MSG_ORDER_SUCCESS = '//*[.="Ваш заказ успешно оформлен"]';

    const TD_SUMM = '//td[@class="col_summ"]';
    const TD_ARTICLE = '//td[contains(@class,"col_article")]';
    const TD_BRAND = '//td[contains(@class, "col_brand")]';
    const TD_NAME = '//td[@class="col_name"]';

    public function getCheckElement()
    {
        return static::$checkElement = self::LOCAL_TABLE;
    }

    /**
     * Убедиться, что после нажатия кнопки в Корзине заказ оформляется
     * Отключение капчи
     */
    public function goToOrder()
    {
        $I = $this->user;
        $I->waitForElement(static::BTN_SAVE_ORDER);
        $this->disableCaptcha();
    }

    /** выбор типа оплаты
     *
     * @param $paymentType
     * @throws \Exception
     */
    public function selectPaymentType($paymentType)
    {
        $this->user->setSelectInClient(self::FLD_PAYMENT ,$paymentType);
    }

    /**
     * Отправить заказ
     * Завершает оформление заказа
     */
    public function saveOrder()
    {
        $I = $this->user;
        $I->click(static::BTN_SAVE_ORDER);
    }

    /**
     * Проверяет сообщение об успехе
     * Возвращает номер заказа
     */
    public function checkOrderSuccess()
    {
        $I = $this->user;
        $I->waitForElement(static::MSG_ORDER_SUCCESS);
    }

    /**
     * Возвращает значение, находящееся в поле цены
     *
     * @param array $searchRow
     * @return mixed
     */
    public function getPositionPrice(array $searchRow)
    {
        $path = $this->getTableRow($searchRow) . self::TD_SUMM;
        return $this->user->grabTextFrom($path);
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
            $name = ' and .' . self::TD_NAME . '[contains(string(), "' . $searchRow['name'] . '")]';
        }

        $row = '//tr[.' . self::TD_ARTICLE . '[normalize-space()="' . $searchRow['article'] . '"] and .'
            . self::TD_BRAND . '[normalize-space()="' . $searchRow['brand'] . '"]' . $name . ']';
        return $row;
    }
}
