<?php

namespace Pages\Client;

/**
 * Проценка в клиентской части
 *
 * Class SearchClientPage
 * @package Pages\Client
 */
class SearchClientPage extends \Pages\ClientPage
{
    const LINK_PAGE = '/search.html';

    /**
     * Блок-фильтр выбора производителя
     * зависит от настройки csUseFirstSearchStep
     */
    const BLOCK_SELECT_BRAND = '//*[contains(@class, "search_alternatives")]';
    const LNK_GO_TO_BASKET = '.b-user-links__a.i-user_basket';

    /** Основные блоки поиска */
    const FORM_SEARCH = '//*[@name="article"]';
    const BTN_SEARCH = '.searchButton';
    const LOADING_ITEM = 'img[src="/images/ajax-loader-big.gif"]';
    const TABLE_RESULTS = 'table.search_results';
    const BLOCK_REQUESTED_CODE = '//*[contains(@class, "section_row")][1]';
    const BLOCK_ANALOGS_UNORIGINAL = '//*[contains(@class, "section_row")][2]';

    /** Блок настроек поиска */
    const FILTER_BRAND_SEARCH = '//*[@id="alternatives_ds"]';
    const LABEL_SEARCH_ANALOGS_CHECK = '//label[@class="custom_checkbox active"]';
    const LABEL_SEARCH_ANALOGS_UNCHECK = '//label[@class="custom_checkbox"]';

    /** Блок заказа */
    const BTN_ORDER = '//a[contains(@href, "/shop/basket.html") and contains(@onclick, "return add_basket")]';

    const MSG_PLEASE_AUTH = '//*[@class="warning_caption"]';

    /** Таблица выбора производителя */
    const BRAND_BLOCK_TD_BRAND = '//td[contains(@class,"col_brand")]';
    const BRAND_BLOCK_TD_SEARCH = '//td[@class="col_action_alt"]//a[.="поиск"]';

    /** Поля таблицы результатов поиска */
    const TD_PRICE = '//td[@class="col_final_price"]';
    const TD_ARTICLE = '//td[contains(@class,"col_article")]';
    const TD_BRAND = '//td[contains(@class, "col_prd_info_link")]';
    const TD_NAME = '//td[@class="col_spare_info"]';
    const TD_DIRECTION = '//td[contains(@class, "col_destination_display")]';

    public function getCheckElement()
    {
        return static::$checkElement = static::BTN_SEARCH;
    }

    /**
     * Выбор чекбокса режима поиска "искать аналоги"
     * @throws \Codeception\Exception\ModuleException
     */
    public function selectSearchWithAnalogs()
    {
        $I = $this->user;
        if ($I->seeElementExistInDom(self::LABEL_SEARCH_ANALOGS_UNCHECK)) {
            $I->click(self::LABEL_SEARCH_ANALOGS_UNCHECK);
            $I->waitForElementVisible(self::LABEL_SEARCH_ANALOGS_CHECK);
        }
    }

    /**
     * Снятие чекбокса режима поиска "искать аналоги"
     * @throws \Codeception\Exception\ModuleException
     */
    public function unselectSearchWithAnalogs()
    {
        $I = $this->user;
        if ($I->seeElementExistInDom(self::LABEL_SEARCH_ANALOGS_CHECK)) {
            $I->click(self::LABEL_SEARCH_ANALOGS_CHECK);
            $I->waitForElementVisible(self::LABEL_SEARCH_ANALOGS_UNCHECK);
        }
    }

    /**
     * Поиск детали по коду
     *
     * @param array $searchRow
     * @throws \Exception
     */
    public function searchByArticle(array $searchRow)
    {
        $I = $this->user;
        $I->seeElement(static::FORM_SEARCH);
        $I->fillField(static::FORM_SEARCH, $searchRow['article']);
        $I->click(static::BTN_SEARCH);

        $I->expect('ожидание загрузки');
        $this->waitForLoading();

        $I->amGoingTo('Если есть таблица выбора производителя, выбрать производителя');
        $this->selectBrandInBlock($searchRow['brand']);

        $I->waitForElementVisible(static::TABLE_RESULTS, 50);
    }

    /**
     * Выбор производителя в блоке производителей до начала поиска
     * Появление блока зависит от включенной настройки
     *
     * @param $brand
     */
    public function selectBrandInBlock($brand)
    {
        $I = $this->user;
        $I->click(
            static::BLOCK_SELECT_BRAND
            . $this->getBrandRowInFilter($brand)
            . self::BRAND_BLOCK_TD_SEARCH
        );
        $this->waitForLoading();
    }

    /**
     * Выдает локатор производителя
     * в блоке выбора производителя до начала поиска
     *
     * @param $brand
     * @return string
     */
    private function getBrandRowInFilter($brand)
    {
        return '//tr[.' . self::BRAND_BLOCK_TD_BRAND . '[normalize-space()="' . $brand . '"]]';
    }

    /**
     * Указать, по какому производителю искать артукул
     * непосредственно на странице поиска
     *
     * @param $brand
     * @throws \Exception
     */
    public function selectBrandInFilter($brand)
    {
        $I = $this->user;
        $I->scrollTo(static::FILTER_BRAND_SEARCH);
        $I->setSelectInClient(static::FILTER_BRAND_SEARCH, $brand);
        $this->waitForLoading();
    }

    /**
     * Проверяет, что поиск осуществляется по определенному производлителю
     *
     * @param $brand
     * @throws \Exception
     */
    public function checkSearchBrandIs($brand)
    {
        $I = $this->user;
        $I->waitForElement(static::FILTER_BRAND_SEARCH . "//*[.='" . $brand . "']");
    }

    /**
     * Ожидание прогрузки бара
     */
    public function waitForLoading()
    {
        $this->user->waitForElementNotVisible(static::LOADING_ITEM, 120);
    }
    
    /**
     * Проверяет, что результаты поиска содержат деталь
     * @param $searchRow - массив с параметрами детали
     */
    public function seeDetail(array $searchRow)
    {
        $I = $this->user;
        $I->seeElement($this->getTableRow($searchRow));
    }

    /**
     * Проверяет, что результаты поиска не содержат деталь
     * @param $searchRow
     */
    public function dontSeeDetail(array $searchRow)
    {
        $I = $this->user;
        $I->dontSeeElement($this->getTableRow($searchRow));
    }

    /**
     * Проверяет направление позиции в поиске
     *
     * @param array $searchRow
     * @throws \Exception
     */
    public function checkDisplayDirection(array $searchRow, $direction)
    {
        $I = $this->user;
        $xpathRow = $this->getTableRow($searchRow) . static::TD_DIRECTION;
        $I->see($direction, $xpathRow);
    }

    /**
     * Добавить в корзину, добавляет деталь в корзину
     *
     * @param array $searchRow
     * @throws \Exception
     */
    public function addToBasket(array $searchRow)
    {
        $I = $this->user;
        $xpathRow = $this->getTableRow($searchRow);
        $I->click($xpathRow . static::BTN_ORDER);
    }

    /**
     * Переход на страницу корзины. Переход по ссылке в шапке
     */
    public function goBasket()
    {
        $this->user->click(self::LNK_GO_TO_BASKET);
    }

    /**
     * Проверяет, что деталь содержится в блоке Неоригинальные аналоги при сгруппированном поиске
     *
     * @param $searchRow
     */
    public function seeDetailInAnalogBlock($searchRow)
    {
        $I = $this->user;

        $xpathRow = $this->getRowInGroupingBlock($searchRow, static::BLOCK_ANALOGS_UNORIGINAL);
        $I->seeElement($xpathRow);
    }

    /**
     * Проверяет, что деталь не содержится в блоке Неоригинальные аналоги при сгруппированном поиске
     *
     * @param $searchRow
     */
    public function dontSeeDetailInAnalogBlock($searchRow)
    {
        $I = $this->user;

        $xpathRow = $this->getRowInGroupingBlock($searchRow, static::BLOCK_ANALOGS_UNORIGINAL);
        $I->dontSeeElement($xpathRow);
    }

    /**
     * Проверяет, что деталь содержится в блоке Запрашиваемый код при сгруппированном поиске
     *
     * @param $searchRow
     */
    public function seeDetailInRequestedCodeBlock($searchRow)
    {
        $I = $this->user;

        $xpathRow = $this->getRowInGroupingBlock($searchRow, static::BLOCK_REQUESTED_CODE);
        $I->seeElement($xpathRow);
    }


    /**
     * Генерирует локатор строки детали в определенном блоке при сгруппированном поиске
     *
     * @param $searchRow - деталь
     * @param $block - локатор блока
     * @return string
     */
    protected function getRowInGroupingBlock($searchRow, $block)
    {
        $xpathRow =
            $block
            . "/following-sibling::"
            . str_replace('//tr', 'tr', $this->getTableRow($searchRow));
        return $xpathRow;
    }

    /**
     * Возвращает значение , находящееся в поле цены
     *
     * @param $searchRow
     * @return mixed
     */
    public function getPositionPrice($searchRow)
    {
        $path = $this->getTableRow($searchRow) . self::TD_PRICE;
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
