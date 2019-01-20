<?php
/**
 *
 *
 * Date: 17.02.16
 * Time: 00:10
 */

namespace Spark\Core\Filter;


use Spark\Http\Request;


interface HttpFilter {
    public function doFilter(Request $request, FilterChain $filterChain);

} 