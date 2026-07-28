<?php

declare(strict_types=1);

function pagination_state(array $source, int $perPage = 12): array
{
    $page = filter_var($source['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
    $perPage = max(1, min($perPage, 50));
    return ['page' => (int) $page, 'per_page' => $perPage, 'offset' => ((int) $page - 1) * $perPage];
}

function pagination_total_pages(int $total, int $perPage): int
{
    return max(1, (int) ceil($total / max(1, $perPage)));
}

function pagination_url(int $page, array $query): string
{
    $query['page'] = $page;
    return '?' . e(http_build_query($query));
}

function pagination_links(int $currentPage, int $totalPages, array $query = []): string
{
    if ($totalPages <= 1) {
        return '';
    }

    $currentPage = max(1, min($currentPage, $totalPages));
    $pages = [1, $totalPages];
    for ($page = max(1, $currentPage - 2); $page <= min($totalPages, $currentPage + 2); $page++) {
        $pages[] = $page;
    }
    $pages = array_values(array_unique($pages));
    sort($pages);

    $html = '<nav aria-label="Pagination"><ul class="pagination justify-content-center">';
    if ($currentPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" rel="prev" href="' . pagination_url($currentPage - 1, $query) . '">Previous</a></li>';
    }

    $previousPage = 0;
    foreach ($pages as $page) {
        if ($previousPage !== 0 && $page > $previousPage + 1) {
            $html .= '<li class="page-item disabled" aria-hidden="true"><span class="page-link">&hellip;</span></li>';
        }
        $active = $page === $currentPage ? ' active' : '';
        $ariaCurrent = $page === $currentPage ? ' aria-current="page"' : '';
        $html .= '<li class="page-item' . $active . '"' . $ariaCurrent . '><a class="page-link" href="' . pagination_url($page, $query) . '">' . $page . '</a></li>';
        $previousPage = $page;
    }

    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" rel="next" href="' . pagination_url($currentPage + 1, $query) . '">Next</a></li>';
    }

    return $html . '</ul></nav>';
}
