<?php


define('ROOT', __DIR__ . '/..' . DIRECTORY_SEPARATOR);
define('VENDOR', ROOT . 'vendor' . DIRECTORY_SEPARATOR);
require VENDOR . 'autoload.php';

use Classes\Collection\EntitiesCollection;
use Classes\Model\Entity;
use Models\Interval;
use \Controllers\Api\IntervalController;

class RangesOptimizationTests extends \PHPUnit_Framework_TestCase
{

    /**
     * @var IntervalController
     */
    private $controller;
    private $model;

    protected function setUp()
    {
        $this->controller = new IntervalController();
        $this->model = new Interval();
    }

    public function testSimpleCreation()
    {
        $range = [
            'date_start' => '2018-01-01',
            'date_end' => '2018-01-30',
            'price' => 101,
            'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 1, 'sat' => 1, 'sun' => 1,
        ];

        $collectionData = [$range];

        $collection = new EntitiesCollection($collectionData, $this->model->getTable());
        $optimized = $this->controller->optimize($collection, $this->model->getTable());
        $items = $optimized->toArray();

        // Single range - should be returned as is
        $this->assertEquals(1, $optimized->count());
        $this->assertEquals($range, $items[0]);
    }

    public function testSplittedCreation()
    {
        $range1 = [
            'date_start' => '2018-01-01',
            'date_end' => '2018-01-30',
            'price' => 101,
            'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 1, 'sat' => 0, 'sun' => 0,
        ];

        $range2 = [
            'date_start' => '2018-01-01',
            'date_end' => '2018-01-30',
            'price' => 101,
            'mon' => 0, 'tue' => 0, 'wed' => 0, 'thu' => 0, 'fri' => 0, 'sat' => 1, 'sun' => 1,
        ];

        $expected = [
            'date_start' => '2018-01-01',
            'date_end' => '2018-01-30',
            'price' => 101,
            'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 1, 'sat' => 1, 'sun' => 1,
        ];


        $collectionData = [$range1, $range2];

        $collection = new EntitiesCollection($collectionData, $this->model->getTable());
        $optimized = $this->controller->optimize($collection, $this->model->getTable());
        $items = $optimized->toArray();

        // Same dates, same prices - should get one grouped range record
        $this->assertEquals(1, $optimized->count());
        $this->assertEquals($expected, $items[0]);
    }

    public function testSplittedDiffPriceCreation()
    {
        $range1 = [
            'date_start' => '2018-01-01',
            'date_end' => '2018-01-30',
            'price' => 101,
            'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 1, 'sat' => 0, 'sun' => 0,
        ];

        $range2 = [
            'date_start' => '2018-01-01',
            'date_end' => '2018-01-30',
            'price' => 102,
            'mon' => 0, 'tue' => 0, 'wed' => 0, 'thu' => 0, 'fri' => 0, 'sat' => 1, 'sun' => 1,
        ];


        $collectionData = [$range1, $range2];

        $collection = new EntitiesCollection($collectionData, $this->model->getTable());
        $optimized = $this->controller->optimize($collection, $this->model->getTable());
        $items = $optimized->toArray();

        // Same dates, different prices - should get 2 range records similar to original
        $this->assertEquals(2, $optimized->count());
        $this->assertEquals($range1, $items[0]);
        $this->assertEquals($range2, $items[1]);
    }

    public function testInsertingRangeInTheMiddleOfExisting()
    {
        $range1 = [
            'date_start' => '2018-01-01',
            'date_end' => '2018-01-30',
            'price' => 101,
            'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 1, 'sat' => 0, 'sun' => 0,
        ];

        $range2 = [
            'date_start' => '2018-01-15',
            'date_end' => '2018-01-25',
            'price' => 102,
            'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 1, 'sat' => 0, 'sun' => 0,
        ];

        $expectedPart1 = [
            'date_start' => '2018-01-01',
            'date_end' => '2018-01-14',
            'price' => 101,
            'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 1, 'sat' => 0, 'sun' => 0,
        ];
        $expectedPart2 = [
            'date_start' => '2018-01-15',
            'date_end' => '2018-01-25',
            'price' => 102,
            'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 1, 'sat' => 0, 'sun' => 0,
        ];
        $expectedPart3 = [
            'date_start' => '2018-01-26',
            'date_end' => '2018-01-30',
            'price' => 101,
            'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 1, 'sat' => 0, 'sun' => 0,
        ];

        $collectionData = [$range1];

        $collection = new EntitiesCollection($collectionData, $this->model->getTable());

        $entity = new Entity($this->model->getTable(), $range2);
        $collection->add($entity);

        $optimized = $this->controller->optimize($collection, $this->model->getTable());
        $items = $optimized->toArray();

        // Interval  should be splitted on 3 parts
        $this->assertEquals(3, $optimized->count());
        $this->assertEquals($expectedPart1, $items[0]);
        $this->assertEquals($expectedPart2, $items[1]);
        $this->assertEquals($expectedPart3, $items[2]);


        // The same action but prices are equal

        $range1 = [
            'date_start' => '2018-01-01',
            'date_end' => '2018-01-30',
            'price' => 101,
            'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 1, 'sat' => 0, 'sun' => 0,
        ];

        $range2 = [
            'date_start' => '2018-01-15',
            'date_end' => '2018-01-25',
            'price' => 101,
            'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 1, 'sat' => 0, 'sun' => 0,
        ];

        $expected = [
            'date_start' => '2018-01-01',
            'date_end' => '2018-01-30',
            'price' => 101,
            'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 1, 'sat' => 0, 'sun' => 0,
        ];

        $collectionData = [$range1];

        $collection = new EntitiesCollection($collectionData, $this->model->getTable());

        $entity = new Entity($this->model->getTable(), $range2);
        $collection->add($entity);

        $optimized = $this->controller->optimize($collection, $this->model->getTable());
        $items = $optimized->toArray();

        // Interval  should not be splitted on 3 parts - original range should be returned
        $this->assertEquals(1, $optimized->count());
        $this->assertEquals($range1, $items[0]);
    }

    public function testConcatenationOfTwoByTheThird()
    {
        $range1 = [
            'date_start' => '2018-01-01',
            'date_end' => '2018-01-10',
            'price' => 10,
            'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 1, 'sat' => 0, 'sun' => 0,
        ];

        $range2 = [
            'date_start' => '2018-01-20',
            'date_end' => '2018-01-30',
            'price' => 10,
            'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 1, 'sat' => 0, 'sun' => 0,
        ];

        $range3 = [
            'date_start' => '2018-01-09',
            'date_end' => '2018-01-21',
            'price' => 10,
            'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 1, 'sat' => 0, 'sun' => 0,
        ];
        $collectionData = [$range1, $range2];

        $collection = new EntitiesCollection($collectionData, $this->model->getTable());

        $optimized = $this->controller->optimize($collection, $this->model->getTable());
        $items = $optimized->toArray();

        $this->assertEquals(2, $optimized->count());
        $this->assertEquals($range1, $items[0]);
        $this->assertEquals($range2, $items[1]);

        $entity = new Entity($this->model->getTable(), $range3);
        $collection->add($entity);
        $optimized = $this->controller->optimize($collection, $this->model->getTable());
        $items = $optimized->toArray();

        $this->assertEquals(1, $optimized->count());

        $expected = [
            'date_start' => '2018-01-01',
            'date_end' => '2018-01-30',
            'price' => 10,
            'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 1, 'sat' => 0, 'sun' => 0,
        ];
        $this->assertEquals($expected, $items[0]);

        // And in the middle with different price

        $collectionData = [$range1, $range2];

        $collection = new EntitiesCollection($collectionData, $this->model->getTable());

        $range3 = [
            'date_start' => '2018-01-09',
            'date_end' => '2018-01-21',
            'price' => 12.40,
            'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 1, 'sat' => 0, 'sun' => 0,
        ];

        $entity = new Entity($this->model->getTable(), $range3);
        $collection->add($entity);
        $optimized = $this->controller->optimize($collection, $this->model->getTable());
        $items = $optimized->toArray();

        $this->assertEquals(3, $optimized->count());

        $expected = array(
            [
                'date_start' => '2018-01-01',
                'date_end' => '2018-01-08',
                'price' => 10,
                'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 1, 'sat' => 0, 'sun' => 0,
            ],
            [
                'date_start' => '2018-01-09',
                'date_end' => '2018-01-21',
                'price' => 12.40,
                'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 1, 'sat' => 0, 'sun' => 0,
            ],
            [
                'date_start' => '2018-01-22',
                'date_end' => '2018-01-30',
                'price' => 10,
                'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 1, 'sat' => 0, 'sun' => 0,
            ],
        );

        $this->assertEquals($expected, $items);

    }


    public function testMoreComplexMiddlePositionedSplitting()
    {
        $range1 = [
            'date_start' => '2018-07-18',
            'date_end' => '2018-07-31',
            'price' => 20,
            'mon' => 1, 'tue' => 1, 'wed' => 1, 'thu' => 1, 'fri' => 1, 'sat' => 0, 'sun' => 0,
        ];

        $range2 = [
            'date_start' => '2018-07-18',
            'date_end' => '2018-07-31',
            'price' => 10,
            'mon' => 0, 'tue' => 0, 'wed' => 0, 'thu' => 0, 'fri' => 0, 'sat' => 1, 'sun' => 1,
        ];

        $collectionData = [$range1];

        $collection = new EntitiesCollection($collectionData, $this->model->getTable());

        $entity = new Entity($this->model->getTable(), $range2);
        $collection->add($entity);

        $optimized = $this->controller->optimize($collection, $this->model->getTable());
        $items = $optimized->toArray();

        // Interval  should be like original
        $this->assertEquals(2, $optimized->count());
        $this->assertEquals($range1, $items[0]);
        $this->assertEquals($range2, $items[1]);


        $range3 = [
            'date_start' => '2018-07-22',
            'date_end' => '2018-07-25',
            'price' => 30,
            'mon' => 0, 'tue' => 0, 'wed' => 1, 'thu' => 1, 'fri' => 0, 'sat' => 0, 'sun' => 0,
        ];

        // in this case we will have (mon, tue, fri) - set with the same price (20) along the whole period
        // and (thu, wed) will be splitted on 3 parts by price (20 [18.07-21.07], 30 [22.07-25.07], 20 [26.07-31.07])
        // and (sat, sun) range will be the same
        // of course in this case there is a possibility of another grouping option:
        // all working days grouped by 20 price, then splitting on 2 groups by price in the middle of the whole range (20/30)
        // and then again all working days with the same price (20)
        // this gives the same amount of ranges (5), so the first splitting method will be in use

        $entity = new Entity($this->model->getTable(), $range3);
        $collection->add($entity);

        $optimized = $this->controller->optimize($collection, $this->model->getTable());
        $items = $optimized->toArray();


        $expected1 = [
            'date_start' => '2018-07-18',
            'date_end' => '2018-07-21',
            'price' => 20,
            'mon' => 0, 'tue' => 0, 'wed' => 1, 'thu' => 1, 'fri' => 0, 'sat' => 0, 'sun' => 0,
        ];

        $expected2 = [
            'date_start' => '2018-07-22',
            'date_end' => '2018-07-25',
            'price' => 30,
            'mon' => 0, 'tue' => 0, 'wed' => 1, 'thu' => 1, 'fri' => 0, 'sat' => 0, 'sun' => 0,
        ];

        $expected3 = [
            'date_start' => '2018-07-18',
            'date_end' => '2018-07-31',
            'price' => 20,
            'mon' => 1, 'tue' => 1, 'wed' => 0, 'thu' => 0, 'fri' => 1, 'sat' => 0, 'sun' => 0,
        ];

        $expected4 = [
            'date_start' => '2018-07-26',
            'date_end' => '2018-07-31',
            'price' => 20,
            'mon' => 0, 'tue' => 0, 'wed' => 1, 'thu' => 1, 'fri' => 0, 'sat' => 0, 'sun' => 0,
        ];

        $expected5 = [
            'date_start' => '2018-07-18',
            'date_end' => '2018-07-31',
            'price' => 10,
            'mon' => 0, 'tue' => 0, 'wed' => 0, 'thu' => 0, 'fri' => 0, 'sat' => 1, 'sun' => 1,
        ];

        $this->assertEquals(5, $optimized->count());
        // TODO the order is not obvious, so comparing arrays ignoring order
        $same = $this->arraysAreDifferentIgnoringOrder(array($expected1,$expected2,$expected3,$expected4,$expected5),$items);
        $this->assertTrue($same);

    }

    /* ---------------------------------------- That`s all the tests for the moment -------------------------------*/

    protected function arraysAreDifferentIgnoringOrder($arr1, $arr2)
    {
        $a = $this->flattenSecondLevelArray($arr1);
        $b = $this->flattenSecondLevelArray($arr2);

        $addFlat = array_diff($a, $b);
        $deleteFlat = array_diff($b, $a);

        $add = [];
        if (!empty($addFlat)) {
            foreach($addFlat as $row) {
                $add[] = json_decode($row, true);
            }
        }

        $delete = [];
        if (!empty($deleteFlat)) {
            foreach($deleteFlat as $row) {
                $delete[] = json_decode($row, true);
            }
        }

        return sizeof($add) == 0 && sizeof($delete) == 0;

    }

    protected function flattenSecondLevelArray($array)
    {
        $flat = [];
        if(!empty($array) && is_array($array)) {
            foreach ($array as $pair) {
                $flat[] = json_encode($pair);
            }
        }
        return $flat;
    }

}

