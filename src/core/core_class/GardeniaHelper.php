<?php
/**
 * [Gardenia Admin] Copyright (c) 2020 https://github.com/qian-xiang/GardeniaAdmin
 * Gardenia Admin is a free software, it under the MIT license, visited https://github.com/qian-xiang/GardeniaAdmin for more details.
 */

namespace gardenia_admin\src\core\core_class;


class GardeniaHelper
{
    static public function layPaginate($data) {
        $request = \request();
        $pageNum = config('app.page_num') ? config('app.page_num') : 10;
        $limit = $request->get('limit') ? $request->get('limit') : $pageNum;
        $page = $request->get('page') ? $request->get('page') : 1;
        $pageCount = ceil(count($data) / $limit);
        if ($page > $pageCount){
            $page = $pageCount;
        }
        $start = ($page - 1) * $limit + 1;
        //该步骤是为了便于理解
        $start = $start - 1;
        $array = array_slice($data,$start, $limit);
        return $array;
    }
}