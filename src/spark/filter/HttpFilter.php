<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 17.02.16
 * Time: 00:10
 */

namespace spark\filter;


use spark\http\Request;


//TODO - rename to HttpFilter
interface HttpFilter {
    function doFilter(Request $request, FilterChain $filterChain);

} 