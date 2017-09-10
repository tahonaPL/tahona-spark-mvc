<?php
/**
 * Created by PhpStorm.
 * User: primosz67
 * Date: 17.02.16
 * Time: 00:10
 */

namespace Spark\Filter;


use Spark\Http\Request;


interface HttpFilter {
    const CLASS_NAME = "Spark\\filter\\HttpFilter";

    public function doFilter(Request $request, FilterChain $filterChain);

} 