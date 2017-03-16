<?php
namespace Virge\Api\Component;
/**
 * API Collection which when serialized created the following structure:
 * 
 * {
 * next: 'url/of/next/api/call',
 * prev: null,
 * rows: []
 * }
 * @author Michael Kramer
 */
class Collection extends \Virge\Core\Model 
{
    public $next;
    public $prev;
    public $rows;

    public static function buildCollection(array $rows = [], int $limit = 25, int $page = 0, string $urlRoot = '/', array $queryParams = [])
    {
        $collection = new static();

        if(count($rows) > $limit) {
            $rows = array_splice($rows, 0, $limit);
            $collection->setNext(static::_buildApiUrl($urlRoot, $page + 1, $queryParams));
        }

        if($page > 0) {
            $collection->setPrev(static::_buildApiUrl($urlRoot, $page - 1, $queryParams));
        }

        $collection->setRows($rows);

        return $collection;
    }

    protected static function _buildApiUrl(string $urlRoot, int $page, array $queryParams = [])
    {
        $queryParams['page'] = $page;
        $queryString = http_build_query($queryParams);

        return sprintf('%s?%s', $urlRoot, $queryString);
    }
}