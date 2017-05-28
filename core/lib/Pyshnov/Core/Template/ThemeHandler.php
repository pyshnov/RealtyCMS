<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */


namespace Pyshnov\Core\Template;


use Detection\MobileDetect;
use Pyshnov\Core\Extension\Extension;
use Pyshnov\Core\Extension\ExtensionDiscovery;
use Pyshnov\Core\Extension\InfoParser;
use Symfony\Component\HttpFoundation\Request;

class ThemeHandler implements ThemeHandlerInterface
{
    protected $rootDir;
    protected $infoParser;
    protected $list;
    protected $desktop;
    protected $smartphone;
    protected $tablet;

    public function __construct($root, InfoParser $info_parser)
    {
        $this->rootDir = $root;
        $this->infoParser = $info_parser;

        $this->smartphone = false;
        $this->desktop = true;
        $this->tablet = false;

    }

    /**
     * {@inheritdoc}
     */
    public function mobileDetect(Request $request, $allow_smartphone = false)
    {
        if($allow_smartphone) {
            $session = $request->getSession();

            $md = new MobileDetect();

            if ($md->isMobile()) {
                $this->desktop = false;
                $this->smartphone = true;
            } elseif ($md->isTablet()) {
                //$this->desktop = false;
                //$this->tablet = true;
            }

            if(null !== $smartphone = $request->get('smartphone')) {
                if($smartphone == 'off') {
                    $smartphone = false;
                } elseif($smartphone == 'on') {
                    $smartphone = true;
                }
            } else {
                if(!$session->has('smartphone')) {
                    if($this->smartphone)
                        $smartphone = true;
                    else
                        $smartphone = false;
                }
            }

            if($smartphone && $this->hasTheme('smartphone')) {
                $session->set('smartphone', 1);

                return true;
            }
        }

        $request->getSession()->set('smartphone', 0);

        return false;
    }

    /**
     * @param string $theme
     * @return array|bool
     */
    public function getThemeDirectory($theme = 'all')
    {
        $themes = $this->getList();

        if ($theme == 'all') {
            $res = [];
            foreach ($themes as $name => $theme) {
                $res[$name] = $theme->getPathname();
            }

            return $res;
        }

        if(isset($themes[$theme])) {
            return $themes[$theme]->getPathname();
        }

        return false;
    }

    /**
     * Вернет массив со всеми темами
     *
     * @return array
     */
    public function getList()
    {
        if (null === $this->list) {
            $this->list = [];

            $listing = new ExtensionDiscovery($this->rootDir);
            $themes =  $listing->scan('theme');

            foreach ($themes as $theme) {
                $info = $this->infoParser->parse($this->rootDir . '/' . $theme->getPathname(). '/info.yml');
                $theme->setInfo($info);
                $this->addTheme($theme);
            }
        }
        return $this->list;
    }

    /**
     * @param Extension $theme
     */
    public function addTheme(Extension $theme)
    {
        $this->list[$theme->getName()] = $theme;
    }

    /**
     * {@inheritdoc}
     */
    public function  getTheme($theme)
    {
        return $this->getList()[$theme] ?? false;
    }

    /**
     * @param $theme
     * @return bool
     */
    public function hasTheme($theme)
    {
        return isset($this->getList()[$theme]);
    }

    /**
     * @return bool
     */
    public function isDesktop(): bool
    {
        return $this->desktop;
    }

    /**
     * @return bool
     */
    public function isSmartphone(): bool
    {
        return $this->smartphone;
    }

    /**
     * @return bool
     */
    public function isTablet(): bool
    {
        return $this->tablet;
    }
}