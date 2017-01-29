<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 17.02.16
 * Time: 00:11
 */

namespace spark\filter;


use spark\http\Request;
use spark\utils\Objects;

class FilterChain {

    /**
     * @var HttpFilter
     */
    private $filter;
    /**
     * @var \Iterator
     */
    private $filters;

    function __construct($filter = null, \Iterator $filters) {
        $this->filters = $filters;
        $this->filter = $filter;

    }

    function doFilter(Request $request) {
        if (Objects::isNotNull($this->filter)) {
            $this->filters->next();
            $filter = $this->filters->current();
            $nextFilterChain = new FilterChain($filter, $this->filters);
            $this->invokeCurrentFilter($request, $nextFilterChain);
        }
    }

    /**
     * @param Request $request
     * @param FilterChain $nextFilterChain
     */
    private function invokeCurrentFilter(Request $request, $nextFilterChain) {
        $this->filter->doFilter($request, $nextFilterChain);
    }

}