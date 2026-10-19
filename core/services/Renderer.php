<?php

declare(strict_types=1);

namespace app\core\services;

use app\core\App;

final readonly class Renderer
{

    public function __construct(private string $controllerName = 'site')
    {
    }

    public function render(string $view, array $params = [], string $layout = 'main'): string
    {
        $pageTitle = $params['pageTitle'] ?? ucfirst($view);

        $params = $this->extractParams($params);

        extract($params);

        $content = $this->renderPartial($view, $params);

        ob_start();
        require VIEWS_PATH . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $layout . '.php';
        $ret = ob_get_clean();

        if (!$ret) {
            App::$logger->warning('Internal error on the server. Contact the administrator. Error 0x' . str_pad((string)__LINE__, 4, "0"));
        }

        return (string)$ret;
    }

    public function renderPartial(string $view, array $params = []): string
    {
        $viewPath = VIEWS_PATH . DIRECTORY_SEPARATOR . $this->controllerName . DIRECTORY_SEPARATOR . "$view.php";

        if (!file_exists($viewPath)) {
            App::$logger->warning("The view $view does not exist. Contact the administrator. Error 0x" . str_pad((string)__LINE__, 4, "0"));
            return "The view <code>$view</code> does not exist. Contact the administrator. Error 0x" . str_pad((string)__LINE__, 4, "0");
        }

        $params = $this->extractParams($params);

        extract($params);

        if (isset($params["statusCode"])) {
            http_response_code($params["statusCode"]);
        }

        ob_start();
        require $viewPath;
        $ret = ob_get_clean();

        if (!$ret) {
            App::$logger->warning('Internal error on the server. Contact the administrator. Error 0x' . str_pad((string)__LINE__, 4, "0"));
        }

        return (string)$ret;
    }

    public static function renderStyles(array $styles): string
    {
        $html = '';
        foreach ($styles as $style) {
            $attributes = self::buildAttributes($style['attributes'] ?? []);

            $cssFile = $style['file'];

//            if (APP_ENV === 'prod') {
//                $cssFile = preg_replace('/\.css$/i', '.min.css', $style['file']);
//            }

            $html .= "<link rel=\"stylesheet\" href=\"$cssFile\"$attributes>\n";
        }
        return $html;
    }

    public static function renderScripts(array $scripts): string
    {
        $html = '';
        foreach ($scripts as $script) {
            $attributes = self::buildAttributes($script['attributes'] ?? []);

            $jsFile = $script['file'];

//            if (APP_ENV === 'prod') {
//                $jsFile = preg_replace('/\.js$/i', '.min.js', $script['file']);
//            }

            $html .= "<script src=\"$jsFile\"$attributes></script>\n";
        }
        return $html;
    }

    private static function buildAttributes(array $attributes): string
    {
        $html = '';
        foreach ($attributes as $key => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $html .= " $key";
                }
            } else {
                $html .= " $key=\"$value\"";
            }
        }
        return $html;
    }

    public static function renderContent(string $view, array $params = []): string
    {
        if (!file_exists($view)) {
            App::$logger->warning("The view $view does not exist. Contact the administrator. Error 0x" . str_pad((string)__LINE__, 4, "0"));
            return "The view <code>$view</code> does not exist. Contact the administrator. Error 0x" . str_pad((string)__LINE__, 4, "0");
        }

        extract($params);
        ob_start();
        require $view;
        $ret = ob_get_clean();

        if (!$ret) {
            App::$logger->warning('Internal error on the server. Contact the administrator. Error 0x' . str_pad((string)__LINE__, 4, "0"));
        }

        return (string)$ret;
    }

    /**
     * @param array $params
     * @return array
     */
    protected function extractParams(array $params): array
    {
        if (!isset($params['styles'])) {
            $params['styles'] = [];
        }
        if (!isset($params['headScripts'])) {
            $params['headScripts'] = [];
        }
        if (!isset($params['scripts'])) {
            $params['scripts'] = [];
        }

        return $params;
    }

}