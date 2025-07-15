<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class IndexService
{
    /**
     * Handle the pagination
     * @param LengthAwarePaginator $data
     * @return array
     */
    public static function handlePagination($data)
    {
        return [
            'perPage' => $data->perPage(),
            'currentPage' => $data->currentPage(),
            "nextPage" => ($data->currentPage() >= $data->lastPage() ? null : $data->currentPage() + 1),
            "prevPage" => ($data->currentPage() <= 1 ? null : $data->currentPage() - 1),
            'lastPage' => $data->lastPage(),
            'from' => ($data->firstItem() === null) ? 0 : $data->firstItem(),
            'to' => ($data->lastItem() === null) ? 0 : $data->lastItem(),
            'total' => $data->total(),
            'pages' => self::pages($data->currentPage(), $data->lastPage()),
        ];
    }

    /**
     * Handle the pagination pages
     * @param int $currentPage
     * @param int $totalPages
     * @return array
     */
    private static function pages($currentPage, $totalPages)
    {
        $maxVisiblePages = 5; 

        $startPage = max($currentPage - floor($maxVisiblePages / 2), 1);
        $endPage = min($startPage + $maxVisiblePages - 1, $totalPages);

        if ($endPage - $startPage + 1 < $maxVisiblePages) {
            $startPage = max($endPage - $maxVisiblePages + 1, 1);
        }

        $visiblePages = range($startPage, $endPage);

        if ($startPage > 1) {
            if($visiblePages[0] != 2)
                array_unshift($visiblePages, 2);
        }
        if ($endPage < $totalPages) {
            $visiblePages[] = $totalPages - 1;
            $visiblePages[] = $totalPages;
        }

        if($visiblePages[0] != 1){
            array_unshift($visiblePages, 1);
        }

        return $visiblePages;
    }


    /**
     * Limit the per page
     * @param int $value
     * @return int
     */
    public static function limitPerPage($value)
    {
        return max(1, min(250, $value));
    }

    /**
     * Check if the page is null
     * @param int $value
     * @return int
     */
    public static function checkPageIfNull($value)
    {
        return ($value === null || !is_numeric($value)) ? 1 : $value;
    }

    /**
     * Check if the search is empty
     * @param string $value
     * @return string
     */
    public static function checkIfSearchEmpty($value)
    {
        return (empty($value) ? null : $value);
    }

    /**
     * Check if the value is empty
     * @param string $value
     * @return string
     */
    public static function checkIfEmpty($value)
    {
        return (empty($value) ? null : $value);
    }

    /**
     * Check if the value is a boolean
     * @param string $value
     * @return bool
     */
    public static function checkIfBoolean($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        $lowercaseValue = strtolower((string) $value);
        if ($lowercaseValue === 'true' || $lowercaseValue === '1' || $value === true || $value === 1) {
            return true;
        } elseif ($lowercaseValue === 'false' || $lowercaseValue === '0' || $value === false || $value === 0) {
            return false;
        }
        
        return null;
    }

    /**
     * Check if the value is a valid number
     * @param string $value
     * @return float|null
     */
    public static function checkIfNumber($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        if (is_numeric($value)) {
            return (float) $value;
        }
        
        return null;
    }

    /**
     * Check if the value is a valid enum value
     * @param string $value
     * @param string $enumClass
     * @return string
     */
    public static function checkIfEnumHasValue($value, string $enumClass)
    {
        if (empty($value)) {
            return null;
        }
    
        $cases = $enumClass::cases();
        foreach ($cases as $case) {
            if ($case->value === $value) {
                return $value;
            }
        }
    
        return null;
    }

    /**
     * Check if the date is valid
     * @param string $date
     * @return string
     */
    public static function checkIfDate($date)
    {
        try {
            $parsedDate = Carbon::parse($date);
            return $parsedDate->format('Y-m-d');
        } catch (\Exception $e) {
            return Carbon::today()->format('Y-m-d');
        }
    }

    public static function checkIfTimestamp($date)
    {
        try {
            $parsedDate = Carbon::parse($date);
            return $parsedDate->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }
}