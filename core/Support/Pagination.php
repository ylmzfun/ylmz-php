<?php

namespace Ylmz\Support;

class Pagination
{
    private int $total;
    private int $perPage;
    private int $current;
    private int $lastPage;
    private string $urlPattern;

    public function __construct(int $total, int $perPage = 20, int $current = 1)
    {
        $this->total = $total;
        $this->perPage = max(1, $perPage);
        $this->current = max(1, $current);
        $this->lastPage = max(1, (int)ceil($this->total / $this->perPage));
        $this->current = min($this->current, $this->lastPage);

        // Build URL pattern from current request
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $query = $_GET;
        unset($query['page']);
        $this->urlPattern = $uri . '?' . http_build_query($query) . '&page={page}';
    }

    public function offset(): int
    {
        return ($this->current - 1) * $this->perPage;
    }

    public function limit(): int
    {
        return $this->perPage;
    }

    public function currentPage(): int
    {
        return $this->current;
    }

    public function lastPage(): int
    {
        return $this->lastPage;
    }

    public function total(): int
    {
        return $this->total;
    }

    public function hasPages(): bool
    {
        return $this->lastPage > 1;
    }

    public function hasMore(): bool
    {
        return $this->current < $this->lastPage;
    }

    /**
     * Render Bootstrap-compatible pagination HTML.
     */
    public function render(): string
    {
        if (!$this->hasPages()) {
            return '';
        }

        $html = '<nav><ul class="pagination">';

        // Previous
        $prevClass = $this->current <= 1 ? ' class="disabled"' : '';
        $html .= "<li{$prevClass}><a href=\"" . $this->url(1) . '">&laquo;</a></li>';

        // Pages
        $start = max(1, $this->current - 2);
        $end = min($this->lastPage, $this->current + 2);

        if ($start > 1) {
            $html .= '<li><a href="' . $this->url(1) . '">1</a></li>';
            if ($start > 2) {
                $html .= '<li class="disabled"><span>...</span></li>';
            }
        }

        for ($i = $start; $i <= $end; $i++) {
            $active = $i === $this->current ? ' class="active"' : '';
            $html .= "<li{$active}><a href=\"" . $this->url($i) . "\">{$i}</a></li>";
        }

        if ($end < $this->lastPage) {
            if ($end < $this->lastPage - 1) {
                $html .= '<li class="disabled"><span>...</span></li>';
            }
            $html .= '<li><a href="' . $this->url($this->lastPage) . '">' . $this->lastPage . '</a></li>';
        }

        // Next
        $nextClass = $this->current >= $this->lastPage ? ' class="disabled"' : '';
        $html .= "<li{$nextClass}><a href=\"" . $this->url($this->lastPage) . '">&raquo;</a></li>';

        $html .= '</ul></nav>';
        return $html;
    }

    private function url(int $page): string
    {
        return str_replace('{page}', (string)$page, $this->urlPattern);
    }
}
