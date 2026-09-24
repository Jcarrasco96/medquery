<?php

declare(strict_types=1);

namespace app\core\services;

use app\core\exceptions\ServerErrorHttpException;

final readonly class Renderer
{

    public function __construct(private string $controllerName = 'site')
    {
    }

    /**
     * @throws ServerErrorHttpException
     */
    public function render(string $view, array $params = [], string $layout = 'main'): string
    {
        $pageTitle = $params['pageTitle'] ?? ucfirst($view);

        extract($params);

        $content = $this->renderPartial($view, $params);

        ob_start();
        require VIEWS_PATH . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $layout . '.php';
        return ob_get_clean() ?: throw new ServerErrorHttpException('Internal error on the server. Contact the administrator. Error 0x0025');
    }

    /**
     * @throws ServerErrorHttpException
     */
    public function renderPartial(string $view, array $params = []): string
    {
        $viewPath = VIEWS_PATH . DIRECTORY_SEPARATOR . $this->controllerName . DIRECTORY_SEPARATOR . "$view.php";

        if (!file_exists($viewPath)) {
//            throw new Exception("The view $viewPath does not exist. 2");
            die("The view $viewPath does not exist. Contact the administrator. Error 0x0002");
        }

        extract($params);

        if (isset($params["statusCode"])) {
            http_response_code($params["statusCode"]);
        }

        ob_start();
        require $viewPath;
        return ob_get_clean() ?: throw new ServerErrorHttpException('Internal error on the server. Contact the administrator. Error 0x0026');
    }

    public static function renderStyles(array $styles): string
    {
        $html = '';
        foreach ($styles as $style) {
            $attributes = self::buildAttributes($style['attributes'] ?? []);

            $cssFile = $style['file'];

            if (APP_ENV === 'prod') {
                $cssFile = preg_replace('/\.css$/i', '.min.css', $style['file']);
            }

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

            if (APP_ENV === 'prod') {
                $jsFile = preg_replace('/\.js$/i', '.min.js', $script['file']);
            }

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

}