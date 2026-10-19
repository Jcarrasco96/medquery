<?php

declare(strict_types=1);

namespace app\core\widgets;

use app\core\services\Pagination;

final class BootstrapPaginator
{

    public static function render(Pagination $p): string
    {
        $html = '<div class="d-flex align-items-center justify-content-between">';
        $html .= '<p class="my-0">Showing <b>' . $p->getBegin() . '</b> - <b>' . $p->getEnd() . '</b> of <b>' . $p->total . '</b> items.</p>';

        $html .= '<div class="d-flex">';

        $html .= '<form method="get">';
        $html .= '<select name="limit" class="form-select mw-50" onchange="this.form.submit();">';
        foreach ([10, 20, 50, 100, 250] as $size) {
            $selected = $p->pageSize == $size ? 'selected' : '';
            $html .= "<option value=\"$size\" $selected>$size</option>";
        }
        $html .= '</select>';
        $html .= '</form>';

        $html .= '<ul class="pagination ms-2 my-0">';

        $pageCount = $p->getPageCount();

        $prev = $p->page - 1;
        $html .= '<li class="page-item' . ($prev < 0 ? ' disabled' : '') . '">';
        $query = http_build_query([
            'page' => max(1, $prev + 1),
            'limit' => $p->pageSize,
            'order' => $p->order,
        ]);
        $html .= '<a class="page-link" href="?' . $query . '">Previous</a>';
        $html .= '</li>';

        $start = max(1, min($p->page + 1 - 2, $pageCount - 4));
        $end = min($pageCount, $start + 4);

        for ($i = $start; $i <= $end; $i++) {
            $query = http_build_query([
                'page' => $i,
                'limit' => $p->pageSize,
                'order' => $p->order,
            ]);

            $active = ($i - 1 == $p->page) ? ' active' : '';
            $html .= '<li class="page-item' . $active . '"><a class="page-link" href="?' . $query . '">' . $i . '</a></li>';
        }

        $next = $p->page + 1;
        $html .= '<li class="page-item' . ($next >= $pageCount ? ' disabled' : '') . '">';
        $query = http_build_query([
            'page' => min($pageCount, $next + 1),
            'limit' => $p->pageSize,
            'order' => $p->order,
        ]);
        $html .= '<a class="page-link" href="?' . $query . '">Next</a>';
        $html .= '</li>';

        $html .= '</ul>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

}