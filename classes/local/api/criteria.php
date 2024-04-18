<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace mod_competvet\local\api;

use mod_competvet\local\persistent\grid;
use mod_competvet\local\persistent\criterion;

/**
 * Criteria API
 *
 * This is a set of API used both locally by mod_competvet and local_competvet
 *
 * @package    mod_competvet
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class criteria {

    /**
     * Update the grid
     *
     * @param int $gridid - The grid id
     * @param string $gridname - The grid name
     * @param int $sortorder - The sort order
     * @param int $type - The type
     * @return int - The grid id
     */
    public static function update_grid(int $gridid, string $gridname, int $sortorder, int $type): int {
        $grid = grid::get_record(['id' => $gridid]);
        if (!$grid) {
            $grid = new grid(0);
            $grid->set('name' , $gridname);
            $grid->set('sortorder', $sortorder);
            $grid->set('type', $type);
            $grid->create();
        } else {
            $grid->set('name' , $gridname);
            $grid->set('sortorder', $sortorder);
            $grid->set('type', $type);
            $grid->update();
        }
        return $grid->get('id');
    }

    /**
     * Delete the grid
     * @param int $gridid - The grid id
     */
    public static function delete_grid(int $gridid): void {
        $grid = grid::get_record(['id' => $gridid]);
        if ($grid) {
            $grid->delete();
        }
        $criteria = criterion::get_records(['evalgridid' => $gridid]);
        foreach ($criteria as $criterion) {
            $criterion->delete();
        }
    }

    /**
     * Update the criterion
     * @param int $criterionid - The criterion id
     * @param string $criterionname - The criterion name
     * @param string $idnumber - The id number
     * @param int $sortorder - The sort order
     * @param int $gridid - The grid id
     * @param int $parentid - The parent id
     * @param int $grade - The grade
     * @return int - The criterion id
     */
    public static function update_criterium(
        int $criterionid,
        string $criterionname,
        string $idnumber,
        int $sortorder,
        int $gridid,
        int $parentid,
        int $grade
    ): int {
        $criterion = criterion::get_record(['id' => $criterionid]);
        if (!$criterion) {
            $criterion = new criterion(0);
            $criterion->set('label' , $criterionname);
            $criterion->set('idnumber', $idnumber);
            $criterion->set('sort', $sortorder);
            $criterion->set('evalgridid', $gridid);
            $criterion->set('parentid', $parentid);
            $criterion->set('grade', $grade);
            $criterion->create();
        } else {
            $criterion->set('label' , $criterionname);
            $criterion->set('idnumber', $idnumber);
            $criterion->set('sort', $sortorder);
            $criterion->set('evalgridid', $gridid);
            $criterion->set('parentid', $parentid);
            $criterion->set('grade', $grade);
            $criterion->update();
        }
        return $criterion->get('id');
    }

    /**
     * Delete the criterion
     * @param int $criterionid - The criterion id
     */
    public static function delete_criterium(int $criterionid): void {
        $criterion = criterion::get_record(['id' => $criterionid]);
        if ($criterion) {
            $criterion->delete();
        }
        $options = criterion::get_records(['parentid' => $criterionid]);
        foreach ($options as $option) {
            $option->delete();
        }
    }
}
