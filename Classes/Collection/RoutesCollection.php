<?php

namespace Classes\Collection;

use Classes\Request\Request;
use Classes\Routing\Route;

/**
 * Class RoutesCollection
 *
 * Collection implementation for application routes keeping
 *
 * @package Classes\Collection
 */
class RoutesCollection extends Collection
{
    /**
     * Returns the route matched to request
     *
     * @param Request $request
     * @return Route|null
     */
    public function findMatch(Request $request)
    {
        foreach (array_reverse($this->items) as $key => $item) {

            /**
             * @var $item Route
             */
            if ($item->match($request->url()) && $item->methodIsAllowed($request->getMethod())) {

                    return $item;
            }
        }

        return null;
    }
}
