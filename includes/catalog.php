<?php

declare(strict_types=1);

/**
 * Build a bounded catalog search condition. Table aliases are fixed by callers,
 * never accepted from request data.
 *
 * @return array{where:string,types:string,params:array<int,string|int>}
 */
function catalog_search_condition(string $titleColumn, string $searchQuery): array
{
    $like = '%' . $searchQuery . '%';
    $where = sprintf(
        '(%s LIKE ? OR c.category_artist LIKE ? OR c.category_album LIKE ? OR c.category_genre LIKE ? OR c.category_language LIKE ?)',
        $titleColumn
    );
    $types = 'sssss';
    $params = [$like, $like, $like, $like, $like];

    if ($searchQuery !== '' && ctype_digit($searchQuery)) {
        $where .= ' OR c.category_year = ?';
        $types .= 'i';
        $params[] = (int) $searchQuery;
    }

    return ['where' => $where, 'types' => $types, 'params' => $params];
}
