<?php

namespace KumbiaPHP\View\Helper;

use KumbiaPHP\View\Helper\AbstractHelper;

/**
 * Description of Tag
 *
 * @author manuel
 */
class Tag extends AbstractHelper
{

    
    protected $includedCss = array();
    protected $includedJs = array();

    

    public function js($src, $priority = 100)
    {
        $this->includedJs[] = compact('src', 'priority');
    }

    public function css($src, $media = 'screen', $priority = 100)
    {
        $this->includedCss[] = compact('src', 'media', 'priority');
    }

    public function includeCss()
    {
        $this->sortByPriority($this->includedCss);
        $code = '';
        foreach (array_unique($this->includedCss, SORT_REGULAR) as $css) {
            $code .= '<link href="' . $this->app->getBaseUrl() . "{$css['src']}.css\" rel=\"stylesheet\" type=\"text/css\" media=\"{$css['media']}\" />" . PHP_EOL;
        }
        return $code;
    }

    public function includeJs()
    {
        $this->sortByPriority($this->includedJs);
        $code = '';
        foreach (array_unique($this->includedJs, SORT_REGULAR) as $js) {
            $code .= '<script type="text/javascript" src="' . $this->app->getBaseUrl() . $js['src'] . '.js"></script>' . PHP_EOL;
        }
        return $code;
    }

    private function sortByPriority(&$array)
    {
        usort($array, function($a, $b) {
                    return ($a['priority'] < $b['priority']) ? -1 : 1;
                }
        );
    }

}