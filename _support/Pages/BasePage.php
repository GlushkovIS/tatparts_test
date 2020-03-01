<?php
/**
 * Created by PhpStorm.
 * User: t.chervyakova
 * Date: 05.11.2019
 * Time: 9:41
 */

namespace Pages;

class BasePage
{

    const LINK_PAGE = '/';

    /** Время ожидания элементов */
    const TIMEOUT = 30;

    /**  Имена коннекторов БД из acceptance.suite */
    const DB_NAME_AR = 'db_ar';
    const DB_NAME_AUTOPRICE = 'db_autoprice';

    /** Формат даты и времени для писем  */
    const MAIL_DATETIME_FORMAT = 'd-m-y_H-i-s';

    protected $downloadPath = 'download';

    /**  Текст в строке уведомлений */
//    const NOTICE_OK = 'успешно';
//    const NOTICE_BACK = 'Вернуться';
//    const NOTICE_EDIT = 'Запись изменена';
    const NOTICE_DELETE = '//div[@class="notice" and contains(text(), "Запись удалена")]';

    /** @var \AcceptanceTester */
    protected $user;

    /** Элемент, по которому определяется, загрузилась ли страница */
    protected static $checkElement;

    public function __construct(\AcceptanceTester $I)
    {
        $this->user = $I;
        $this->downloadPath = codecept_output_dir() . $this->downloadPath;
    }

    /**
     * базовый переход на страницу
     *
     * @param $element
     * @param $pageLink
     * @throws \Exception
     */
    public function goPageParams($element, $pageLink)
    {
        $I = $this->user;
        $I->amOnPage($pageLink);
        $I->dontSeeInTitle('Error');
        $I->dontSeeInTitle('Warning');
        $I->waitForElement($element);
        $I->expect('страница прогрузилась');
    }

    /**
     * Переход на страницу для всех страниц PageObject
     */
    public function goPage()
    {
        $this->goPageParams($this->getCheckElement(), static::LINK_PAGE);
    }

    public function getCheckElement()
    {
        return static::$checkElement;
    }

    /**
     * Берет текущее время. Учитывает временную зону - Europe/Moscow
     */
    public function getDateTime()
    {
        $I = $this->user;
        return $I->getDateTime();
    }

    /**
     * Проверяет наличие письма в папке _debug. Ищет письмо по полному совпадению имени
     * Возвращает булево значение
     *
     * @param $email
     * @param $dateTime - время примерного создания письма. Получать лучше с помощью функции getDateTime из BasePage
     *
     * @return bool
     * @throws \Codeception\Exception\ModuleException
     */
    public function checkMail($email, $dateTime)
    {
        $I = $this->user;

        $mailFormat = strtolower($email);
        $mailFormat = str_replace('@', '_', $mailFormat);
        $mailFormat = 'mail_' . $mailFormat;

        $i = 0;
        $responseCode = 400;
        $pathFileName = $I->getBaseUrl() . '/_debug/' . $mailFormat . '_';

        /** т.к.мы не знаем точное время создания файла, в имени файла меняются секунды, интервал - 20 секунд */
        while ($responseCode !== 200 & $i < 20) {
            $formatDate = date(static::MAIL_DATETIME_FORMAT, date_timestamp_get($dateTime) + $i);
            $path = $pathFileName . $formatDate . '_0.html';
            $responseCode = $this->getResponseCodeDoc($path);
            $i++;
        }

        if ($responseCode === 200) {
            return true;
        }

        return false;
    }

    /**
     * Получает код ответа от документа по ссылке
     *
     * @param string $fileName - ссылка на файл
     *
     * @return mixed - код ответа
     */
    public function getResponseCodeDoc($fileName)
    {
        $I = $this->user;
        $result = $I->executeAsyncJS("
		var uri = arguments[0];
		var callback = arguments[1];
		var xhr = new XMLHttpRequest();
		xhr.responseType = 'arraybuffer';
		xhr.onload = function(){ callback(xhr.status) };
		xhr.open('GET', uri);
		xhr.send();
		", [$fileName]);

        return $result;
    }
}