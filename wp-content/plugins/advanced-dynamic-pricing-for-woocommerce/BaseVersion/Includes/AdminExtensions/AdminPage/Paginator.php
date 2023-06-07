<?php

namespace ADP\BaseVersion\Includes\AdminExtensions\AdminPage;

defined('ABSPATH') or exit;

class Paginator
{
    protected $totalItems = 0;
    protected $totalPages = 0;

    /**
     * @param int $value
     */
    public function setTotalItems($value)
    {
        $this->totalItems = (int)$value;
    }

    /**
     * @param int $value
     */
    public function setTotalPages($value)
    {
        $this->totalPages = (int)$value;
    }

    public static function getPageNum()
    {
        $page = 1;
        if ( ! empty($_GET['paged'])) {
            $page = (int)stripslashes_deep($_GET['paged']);
        }

        return $page;
    }

    public function makeHtml()
    {
        $which      = 'top';
        $totalItems = $this->totalItems;
        $totalPages = $this->totalPages;

        $output = '<span class="displaying-num">' . sprintf(_n('%s item', '%s items', $totalItems),
                number_format_i18n($totalItems)) . '</span>';

        $current            = self::getPageNum();
        $removableQueryArgs = wp_removable_query_args();

        $currentUrl = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

        $currentUrl = remove_query_arg($removableQueryArgs, $currentUrl);

        $pageLinks = array();

        $totalPagesBefore = '<span class="paging-input">';
        $totalPagesAfter  = '</span></span>';

        $disableFirst = $disableLast = $disablePrev = $disableNext = false;

        if ($current == 1) {
            $disableFirst = true;
            $disablePrev  = true;
        }
        if ($current == 2) {
            $disableFirst = true;
        }
        if ($current == $totalPages) {
            $disableLast = true;
            $disableNext = true;
        }
        if ($current == $totalPages - 1) {
            $disableLast = true;
        }

        if ($disableFirst) {
            $pageLinks[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
        } else {
            $pageLinks[] = sprintf(
                "<a class='first-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url(remove_query_arg('paged', $currentUrl)),
                __('First page'),
                '&laquo;'
            );
        }

        if ($disablePrev) {
            $pageLinks[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
        } else {
            $pageLinks[] = sprintf(
                "<a class='prev-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url(add_query_arg('paged', max(1, $current - 1), $currentUrl)),
                __('Previous page'),
                '&lsaquo;'
            );
        }

        if ('bottom' === $which) {
            $htmlCurrentPage  = $current;
            $totalPagesBefore = '<span class="screen-reader-text">' . __('Current Page') . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
        } else {
            $htmlCurrentPage = sprintf(
                "%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label for="current-page-selector" class="screen-reader-text">' . __('Current Page') . '</label>',
                $current,
                strlen($totalPages)
            );
        }
        $htmlTotalPages = sprintf("<span class='total-pages'>%s</span>", number_format_i18n($totalPages));
        $pageLinks[]    = $totalPagesBefore . sprintf(_x('%1$s of %2$s', 'paging'), $htmlCurrentPage,
                $htmlTotalPages) . $totalPagesAfter;

        if ($disableNext) {
            $pageLinks[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
        } else {
            $pageLinks[] = sprintf(
                "<a class='next-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url(add_query_arg('paged', min($totalPages, $current + 1), $currentUrl)),
                __('Next page'),
                '&rsaquo;'
            );
        }

        if ($disableLast) {
            $pageLinks[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
        } else {
            $pageLinks[] = sprintf(
                "<a class='last-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url(add_query_arg('paged', $totalPages, $currentUrl)),
                __('Last page'),
                '&raquo;'
            );
        }

        $paginationLinksClass = 'pagination-links';
        if ( ! empty($infinite_scroll)) {
            $paginationLinksClass .= ' hide-if-js';
        }
        $output .= "\n<span class='$paginationLinksClass'>" . join("\n", $pageLinks) . '</span>';

        if ($totalPages) {
            $pageClass = $totalPages < 2 ? ' one-page' : '';
        } else {
            $pageClass = ' no-pages';
        }

        return "<div class='tablenav-pages{$pageClass}'>$output</div>";
    }
}
