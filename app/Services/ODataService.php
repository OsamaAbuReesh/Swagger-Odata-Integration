<?php

namespace App\Services;

use Illuminate\Http\Request;

class ODataService
{
    protected $data;

    public function __construct()
    {
        $this->data = [
            ['id' => 1, 'title' => 'Understanding AI', 'content' => 'Content about AI', 'user_id' => 1],
            ['id' => 2, 'title' => 'Football World Cup', 'content' => 'Content about Football', 'user_id' => 2],
            ['id' => 3, 'title' => 'Advancements in AI', 'content' => 'Content about AI advancements', 'user_id' => 1],
            ['id' => 4, 'title' => 'Social Media Trends', 'content' => 'Content about Social Media', 'user_id' => 3],
            ['id' => 5, 'title' => 'Football Tactics', 'content' => 'Content about Football tactics', 'user_id' => 2],
        ];
    }

    public function handle(Request $request)
    {
        $filteredData = $this->data;

        if ($request->has('$filter')) {
            // Parse and apply the filter
            $filter = $request->input('$filter');
            // Implement basic filtering logic (example)
            $filteredData = array_filter($filteredData, function ($item) use ($filter) {
                // Example filter: id eq 1
                parse_str(str_replace(' eq ', '=', $filter), $conditions);
                foreach ($conditions as $key => $value) {
                    if ($item[$key] != $value) {
                        return false;
                    }
                }
                return true;
            });
        }

        if ($request->has('$orderby')) {
            // Parse and apply the orderby
            $orderby = $request->input('$orderby');
            // Implement basic sorting logic (example)
            usort($filteredData, function ($a, $b) use ($orderby) {
                // Example orderby: title asc
                list($key, $direction) = explode(' ', $orderby);
                if ($direction == 'asc') {
                    return $a[$key] <=> $b[$key];
                } else {
                    return $b[$key] <=> $a[$key];
                }
            });
        }

        if ($request->has('$top')) {
            // Parse and apply the top
            $top = (int) $request->input('$top');
            $filteredData = array_slice($filteredData, 0, $top);
        }

        if ($request->has('$skip')) {
            // Parse and apply the skip
            $skip = (int) $request->input('$skip');
            $filteredData = array_slice($filteredData, $skip);
        }

        return $filteredData;
    }
}
