<?php namespace App\Yantrana\Base;

use App\Yantrana\__Laraware\Core\CoreRepository;

abstract class BaseRepository extends CoreRepository
{
    
    // Except this columns & return another columns.
    protected $exceptColumns = [];

    // Exclude only this columns.
    protected $onlyColumns   = [];

    /**
      * Except columns
      *
      * @param array $columns
      *
      * @return array
      *-----------------------------------------------------------------------*/

    public function exceptColumns(array $exceptColumns)
    {
        $this->exceptColumns = $exceptColumns;

        return $this;
    }

    /**
    * Only this columns
    *
    * @param array $columns
    *
    * @return array
    *-----------------------------------------------------------------------*/

    public function onlyColumns(array $onlyColumns)
    {
        $this->onlyColumns = $onlyColumns;

        return $this;
    }
}
