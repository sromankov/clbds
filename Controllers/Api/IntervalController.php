<?php

namespace Controllers\Api;

use Classes\Collection\EntitiesCollection;
use Classes\Model\Entity;
use Classes\Request\Request;
use Models\Interval;

/**
 * Class IntervalController
 *
 * Api Controller
 *
 * @package Controllers\Api
 */
class IntervalController
{
    /**
     * May be not the base place to keep this keys set and optimization methods
     * @var array
     */
    public static $pricesKeys = ['mon','tue','wed','thu','fri','sat','sun'];

    /**
     * Returns intervals list
     *
     * @param Request $request
     * @return \Classes\Response\JsonResponse
     */
    public function index(Request $request)
    {
        $model = new Interval();
        $ranges = $model->all()->toArray();

        return json(['status' => true, 'data' => ['ranges' => $ranges]]);
    }

    /**
     * Rebuilds intervals set on new interval appearance
     *
     * @param Request $request
     * @return \Classes\Response\JsonResponse
     */
    public function create(Request $request)
    {
        $errors = $this->validateRequest($request);
        if (sizeof($errors)) {
            return json(['status' => false, 'data' => ['errors' => $errors]]);
        }

        $model = new Interval();
        /**
         * @var $currentList EntitiesCollection
         */
        $currentList = $model->all();
        $tmpList = $currentList->makeClone();

        $entity = new Entity($model->getTable(), $request->attributes->all());
        $tmpList->add($entity);

        $newList = $this->optimize($tmpList, $model->getTable());
        $currentList->drop();
        $newList->apply();

        return json(['status' => true, 'data' => []]);
    }

    /**
     * Returns interval data by ID
     *
     * @param Request $request
     * @param $id
     * @return \Classes\Response\JsonResponse
     */
    public function read(Request $request, $id)
    {
        $model = new Interval();
        $range = $model->find($id);

        return json(['status' => true, 'data' => ['range' => $range->toArray()]]);
    }

    /**
     * Rebuilds intervals set on existing interval update
     *
     * @param Request $request
     * @param $id
     * @return \Classes\Response\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $errors = $this->validateRequest($request);
        if (sizeof($errors)) {
            return json(['status' => false, 'data' => ['errors' => $errors]]);
        }

        $model = new Interval();
        /**
         * @var $currentList EntitiesCollection
         */
        $currentList = $model->all();
        $excluded = $currentList->excludeById($id);

        $entity = new Entity($model->getTable(), $request->attributes->all());
        $excluded->add($entity);

        $newList = $this->optimize($excluded, $model->getTable());

        $currentList->drop();

        $newList->apply();

        return json(['status' => true, 'data' => []]);
    }


    /**
     * Rebuilds intervals set on existing interval removing
     *
     * @param Request $request
     * @param $id
     * @return \Classes\Response\JsonResponse
     */
    public function delete(Request $request, $id)
    {
        $model = new Interval();
        /**
         * @var $currentList EntitiesCollection
         */
        $currentList = $model->all();
        $excluded = $currentList->excludeById($id);

        $newList = $this->optimize($excluded, $model->getTable());

        $currentList->drop();

        $newList->apply();

        return json(['status' => true, 'data' => []]);
    }

    /**
     * Not the best validation method. TODO implement Validators
     *
     * @param Request $request
     * @return array
     */
    protected function validateRequest(Request $request)
    {
        $errors = [];
        $attributes = $request->attributes->all();

        if (!($attributes['price'] >0))
        {
            $errors['price'] = 'Price should be greater than 0';
        }

        $allDaysEmpty = true;
        foreach (self::$pricesKeys as $pricesKey) {
            if ($attributes[$pricesKey] == 1) {
                $allDaysEmpty = false;
            }
        }

        if ($allDaysEmpty) {
            $errors['days'] = 'Please select ay least one day';
        }

        return $errors;
    }

    /* --------------------- Below is an optimization part -------------*/

    /**
     * Executes 3-steps optimization
     *
     * @param EntitiesCollection $originalCollection
     * @param $model
     * @return EntitiesCollection
     */
    public function optimize(EntitiesCollection $originalCollection, $model)
    {
        $sevenPricesRanges  = $this->getSevenPricesRanges($originalCollection);
        $perWeekDayRanges   = $this->getPerWeekDayRanges($sevenPricesRanges);
        $realRanges         = $this->mergePerWeekDayRanges($perWeekDayRanges);

        return new EntitiesCollection($realRanges, $model);
    }

    /**
     * The first stage of intervals set optimization
     * builds ranges with all week days prices with "price not changed" dates ranges
     *
     * @param EntitiesCollection $currentList
     * @return array
     */
    protected function getSevenPricesRanges(EntitiesCollection $currentList)
    {
        $datesLine = [];

        foreach ($currentList as $item) {

            $attributes = $item->toArray();

            if (isset($attributes['date_start'])) {
                $datesLine[] = $attributes['date_start'];
            }
            if (isset($attributes['date_end'])) {
                $datesLine[] = $attributes['date_end'];
            }
        }

        foreach ($datesLine as $date) {

            $datesLine[] = date('Y-m-d', strtotime($date .' +1 day'));
        }

        $datesLine = array_unique($datesLine);
        sort($datesLine);

        $sevenPricesRanges = [];

        foreach (self::$pricesKeys as $key) {
            $momentPrices[$key] = null;
        }

        $prevMomentPrices = $momentPrices;

        foreach ($datesLine as $dateForSevenRange) {

            $momentPrices = $prevMomentPrices;

            foreach (self::$pricesKeys as $key) {
                $momentPricesExists[$key] = false;
            }

            foreach ($currentList as $item) {

                $attributes = $item->toArray();

                if (date($attributes['date_start']) <= date($dateForSevenRange) &&
                    date($attributes['date_end']) >= date($dateForSevenRange)) {

                    $price = $attributes['price'];

                    foreach (self::$pricesKeys as $key) {

                        if ($attributes[$key] == 1) {
                            $momentPrices[$key] = $price;
                            $momentPricesExists[$key] = true;
                        }
                    }
                }
            }

            foreach (self::$pricesKeys as $key) {
                $momentPrices[$key] = $momentPricesExists[$key] ? $momentPrices[$key] : null;
            }

            if (array_diff_assoc($momentPrices, $prevMomentPrices)) {
                $sevenPricesRanges[] = ['prices' => $momentPrices, 'start_date'=> $dateForSevenRange];
                $prevMomentPrices = $momentPrices;
            }
        }

        return $sevenPricesRanges;
    }


    /**
     * The second stage of intervals optimization
     * Returns per-week-day intervals
     *
     * @param $sevenPricesRanges
     * @return array
     */
    protected function getPerWeekDayRanges($sevenPricesRanges)
    {
        $perWeekDayRanges = [];

        foreach (self::$pricesKeys as $key) {
            $momentPrices[$key] = null;
            $momentStartDates[$key] = null;
        }

        foreach ($sevenPricesRanges as $sevenPricesRange) {

            $sevenPricesRangePrices = $sevenPricesRange['prices'];
            $sevenPricesRangeStartDate = $sevenPricesRange['start_date'];

            foreach (self::$pricesKeys as $key) {

                if (is_null( $momentStartDates[$key]) && !is_null($sevenPricesRangePrices[$key]))
                {
                    $momentStartDates[$key] = $sevenPricesRangeStartDate;
                    $momentPrices[$key] = $sevenPricesRangePrices[$key];
                }

                if (!is_null( $momentStartDates[$key]) ){

                    if ($momentPrices[$key] != $sevenPricesRangePrices[$key]) {
                        $dayBefore = date('Y-m-d', strtotime($sevenPricesRangeStartDate .' -1 day'));

                        if (!is_null($momentPrices[$key])) {

                            $record = [
                                'date_start' => $momentStartDates[$key],
                                'date_end' => $dayBefore,
                                'price' => $momentPrices[$key]
                            ];
                            foreach (self::$pricesKeys as $weekDay) {
                                $record[$weekDay] = $weekDay == $key ? 1 : 0;
                            }

                            $perWeekDayRanges[] = $record;
                        }


                        $momentStartDates[$key] = $sevenPricesRangeStartDate;
                        $momentPrices[$key] = $sevenPricesRangePrices[$key];
                    }
                }
            }
        }

        return $perWeekDayRanges;
    }

    /**
     * The third optimization stage
     * Merges intervals matched by start date, end date and price into single intervals
     *
     * @param $perWeekDayRanges
     * @return array
     */
    protected function mergePerWeekDayRanges($perWeekDayRanges)
    {
        $realRanges = [];

        foreach ($perWeekDayRanges as $perWeekDayRange) {

            $dateStart =  $perWeekDayRange['date_start'];
            $dateEnd   =  $perWeekDayRange['date_end'];
            $price     =  $perWeekDayRange['price'];

            $found = false;

            foreach ($realRanges as $key => $realRange) {

                if ($realRange['date_start'] == $dateStart && $realRange['date_end'] == $dateEnd && $realRange['price'] == $price) {

                    foreach (self::$pricesKeys as $weekDay) {

                        if ($perWeekDayRange[$weekDay] == 1) {
                            $realRanges[$key][$weekDay] = 1;
                        }
                    }

                    $found = true;
                }
            }

            if (!$found) {
                $record = [
                    'date_start' => $dateStart,
                    'date_end' => $dateEnd,
                    'price' => $price
                ];
                foreach (self::$pricesKeys as $weekDay) {
                    $record[$weekDay] = $perWeekDayRange[$weekDay];
                }

                $realRanges[] = $record;
            }
        }

        return $realRanges;
    }
}