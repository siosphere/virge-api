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
class Collection extends \Virge\Core\Model {
    public $next;
    public $prev;
    public $rows;
}