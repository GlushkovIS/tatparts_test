<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class OpenPage extends \Codeception\Module
{
    /**
     * Get response status code
     * @return mixed
     * @throws \Codeception\Exception\ModuleException
     */
    public function getResponseStatusCode()
    {
        return $this->getModule('PhpBrowser')->_getResponseStatusCode();
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
    public function seeElementExist($element)
    {
        $flag = false;
        /** @var \Symfony\Component\DomCrawler\Crawler $elements */
        $elements = $this->getModule('PhpBrowser')->_findElements($element);
        if (count($elements) > 0) {
            $flag = true;
        }
        return $flag;
    }
}
