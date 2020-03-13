<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends \Codeception\Module
{
    /**
     * Get base url
     * @return mixed
     * @throws \Codeception\Exception\ModuleException
     */
    public function getBaseUrl()
    {
        return $this->getModule('WebDriver')->_getUrl();
    }

    /**
     * Get current url from WebDriver
     * @return mixed
     * @throws \Codeception\Exception\ModuleException
     */
    public function getCurrentUrl()
    {
        return $this->getModule('WebDriver')->_getCurrentUri();
    }

    public function isSelected($selector)
    {
        $elements = $this->getModule('WebDriver')->_findElements($selector);
        /**@var $el \Facebook\WebDriver\WebDriverElement */
        $el = $elements[0];
        return $el->isSelected();
    }

    /**
     * Берет текущее время. Учитывает временную зону - Europe/Moscow
     *
     * @return \DateTime|false
     */
    public function getDateTime()
    {
        date_default_timezone_set('Europe/Moscow');
        return date_create();
    }

    /**
     * Check that element exist on page
     * Get boolean value
     *
     * @param $element
     *
     * @return bool
     * @throws \Codeception\Exception\ModuleException
     */
    public function seeElementExistInDom($element)
    {

        /**@var $table \Facebook\WebDriver\WebDriverElement */
        $elements = $this->getModule('WebDriver')->_findElements($element);
        try {
            $this->assertNotEmpty($elements);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Clears the cookies
     * @return mixed
     * @throws \Codeception\Exception\ModuleException
     */
    function clearAllCookies()
    {
        return $this->getModule('WebDriver')->webDriver->manage()->deleteAllCookies();
    }
}
