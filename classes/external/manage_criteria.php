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

namespace mod_competvet\external;

use external_api;
use external_description;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use external_warnings;
use mod_competvet\local\api\criteria;

DEFINE('COMPETVET_CRITERIA_EVALUATION', 1);
DEFINE('COMPETVET_CRITERIA_CERTIFICATION', 2);
DEFINE('COMPETVET_CRITERIA_LIST', 3);

$exampleJsonEval = '
{
    "grids": [
        {
            "gridname": "YEAR2024",
            "gridid": 1,
            "criteria": [
                {
                    "criteriumid": 1,
                    "idnumber": "Q001",
                    "title": "Savoir être",
                    "sortorder": 1,
                    "edit": false,
                    "haschanged": false,
                    "hasoptions": true,
                    "options": [
                        {
                            "optionid": 1,
                            "idnumber": "Q002",
                            "title": "Respect des horaires de travail",
                            "sortorder": 1
                        },
                        {
                            "optionid": 2,
                            "idnumber": "Q003",
                            "title": "Respect des consignes",
                            "sortorder": 2
                        },
                    ]
                }
            ]
        }
    ]
}
';
$exampleJsonCert = '
{
    "grids": [
        {
            "gridname": "YEAR2024",
            "gridid": 1,
            "criteria": [
                {
                    "criteriumid": 1,
                    "title": "Réaliser un examen clinique complet (comprenant entre autres une palpation des thyroïdes et un toucher rectal)",
                    "sortorder": 1
                },
                {
                    "criteriumid": 2,
                    "title": "Formuler un bilan anamnestique et clinique en retenant et en hiérarchisant les signes cliniques pertinents",
                    "sortorder": 2
                },
                {
                    "criteriumid": 3,
                    "title": "Formuler des hypothèses diagnostiques hiérarchisées à la lumière du contexte anamnestique et clinique",
                    "sortorder": 3
                },
                {
                    "criteriumid": 4,
                    "title": "Décider de la nécessité d\'une hospitalisation sur la base d\'anomalies cliniques ou biologiques",
                    "sortorder": 4
                },
                {
                    "criteriumid": 5,
                    "title": "Prescrire et administrer un protocole de fluidothérapie adapté pour un animal hospitalisé",
                    "sortorder": 5
                }
                ]
            }
            ]
        }
';

$exampleJsonList = '
{
    "grids": [
        {
            "gridname": "YEAR2024",
            "gridid": 1,
            "criteria": [
                {
                    "criteriumid": 1,
                    "title": "Nombre et diversité des cas",
                    "sortorder": 1,
                    "edit": false,
                    "haschanged": false,
                    "hasoptions": true,
                    "options": [
                        {
                            "optionid": 1,
                            "title": "Le nombre de saisis par l\'étudiant est insuffisant",
                            "sortorder": 1,
                            "hasgrade": true,
                            "grade": 0
                        },
                        {
                            "optionid": 2,
                            "title": "Le nombre de cas saisis par l\'étudiant est suffisant",
                            "sortorder": 2,
                            "hasgrade": true,
                            "grade": 12.5
                        }
                    ]
                }
            ]
        }
    ]
}
';


$dbfieldscriteria = [
    'id' => 'int',
    'label' => 'text',
    'idnumber' => 'char',
    'parentid' => 'int',
    'sort' => 'int',
    'evalgridid' => 'int',
    'type' => 'int',
    'usermodified' => 'int',
    'timecreated' => 'int',
    'timemodified' => 'int',
];

/**
 * Class manage_criteria
 * Webservice class for managing criteria
 *
 * @package    mod_competvet
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage_criteria extends external_api {
    /**
     * Returns description of method parameters. This will be used to validate the JSON data sent to the external function. It
     * Will need to allow the JSON objects for $exampleJsonEval, $exampleJsonCert and $exampleJsonList and it will include the
     * gridid and the type of criteria to manage.
     *
     * @return external_function_parameters
     */
    public static function update_parameters(): external_function_parameters {
        return new external_function_parameters([
            'grids' => new external_multiple_structure(
                new external_single_structure([
                    'gridid' => new external_value(PARAM_INT, 'The grid id', VALUE_REQUIRED),
                    'gridname' => new external_value(PARAM_TEXT, 'The name of the grid', VALUE_OPTIONAL),
                    'sortorder' => new external_value(PARAM_INT, 'The sort order of the grid', VALUE_OPTIONAL),
                    'haschanged' => new external_value(PARAM_BOOL, 'Has the grid changed', VALUE_OPTIONAL),
                    'deleted' => new external_value(PARAM_BOOL, 'Is the grid deleted', VALUE_OPTIONAL),
                    'criteria' => new external_multiple_structure(
                        new external_single_structure([
                            'criteriumid' => new external_value(PARAM_INT, 'The criterium id', VALUE_REQUIRED),
                            'title' => new external_value(PARAM_TEXT, 'The title of the criterium', VALUE_REQUIRED),
                            'idnumber' => new external_value(PARAM_TEXT, 'The id number of the criterium', VALUE_REQUIRED),
                            'sortorder' => new external_value(PARAM_INT, 'The sort order of the criterium', VALUE_REQUIRED),
                            'haschanged' => new external_value(PARAM_BOOL, 'Has the criterium changed', VALUE_REQUIRED),
                            'deleted' => new external_value(PARAM_BOOL, 'Is the criterium deleted', VALUE_OPTIONAL),
                            'hasoptions' => new external_value(PARAM_BOOL, 'Does the criterium have options', VALUE_OPTIONAL),
                            'options' => new external_multiple_structure(
                                new external_single_structure([
                                    'optionid' => new external_value(PARAM_INT, 'The option id', VALUE_REQUIRED),
                                    'idnumber' => new external_value(PARAM_TEXT, 'The id number of the option', VALUE_REQUIRED),
                                    'title' => new external_value(PARAM_TEXT, 'The title of the option', VALUE_REQUIRED),
                                    'sortorder' => new external_value(PARAM_INT, 'The sort order of the option', VALUE_REQUIRED),
                                    'hasgrade' => new external_value(PARAM_BOOL, 'Does the option have a grade', VALUE_OPTIONAL),
                                    'grade' => new external_value(PARAM_FLOAT, 'The grade of the option', VALUE_OPTIONAL),
                                    'deleted' => new external_value(PARAM_BOOL, 'Is the option deleted', VALUE_OPTIONAL),
                                ])
                            )
                        ])
                    )
                ])
            ),
            'type' => new external_value(PARAM_INT, 'The type of criteria to manage', VALUE_REQUIRED)
        ]);
    }

    /**
     * Update the criteria
     *
     * @param array $grids
     * @param int $type
     * @return array
     */
    public static function update($grids, $type): array {
        global $DB;
        $params = self::validate_parameters(self::update_parameters(), ['grids' => $grids, 'type' => $type]);

        $grids = $params['grids'];
        $type = $params['type'];
        $warnings = [];
        $results = [];

        // Loop through the grids, if a grid has the haschanged flag set to true,
        // update or insert the grid by calling the correct API.
        foreach ($grids as $grid) {
            if ($grid['deleted']) {
                criteria::delete_grid($grid['gridid']);
                continue;
            }
            $gridid = $grid['gridid'];
            if ($grid['haschanged']) {
                $gridid = criteria::update_grid(
                    $grid['gridid'],
                    $grid['gridname'],
                    $grid['sortorder'],
                    $type
                );
            }
            foreach ($grid['criteria'] as $criterium) {
                if ($criterium['deleted']) {
                    $result = criteria::delete_criterium($criterium['criteriumid']);
                    if ($result) {
                        $results[] = $result;
                    }
                    continue;
                }
                if ($criterium['haschanged']) {
                    $criteriumid = criteria::update_criterium(
                        $criterium['criteriumid'],
                        $criterium['title'],
                        $criterium['idnumber'],
                        $criterium['sortorder'],
                        $gridid,
                        0,
                        0,
                    );
                    if ($criterium['hasoptions']) {
                        foreach ($criterium['options'] as $option) {
                            if ($option['deleted']) {
                                $result = criteria::delete_criterium($option['optionid']);
                                if ($result) {
                                    $results[] = $result;
                                    $result = false;
                                }
                            }
                            $result = criteria::update_criterium(
                                $option['optionid'],
                                $option['title'],
                                $option['idnumber'],
                                $option['sortorder'],
                                $gridid,
                                $criteriumid,
                                $option['grade'],
                            );
                            if ($result) {
                                $results[] = $result;
                                $result = false;
                            }
                        }
                    }
                }
            }

        }

        if (count($results) === 0) {
            $result = true;
        } else {
            $result = false;
        }
        $warnings = array_map(function ($warning) {
            return [
                'item' => $warning,
                'warningcode' => 'exception',
                'message' => 'An exception occurred',
            ];
        }, $warnings);

        return [
            'result' => $result,
            'warnings' => $warnings,
        ];
    }

    /**
     * Returns description of method return value
     *
     * @return external_description
     */
    public static function update_returns(): external_single_structure {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'The processing result'),
            'warnings' => new external_warnings()
        ]);
    }

    /**
     * Returns description of method parameters
     * Look at the example_json for the structure of the data
     *
     * @return external_function_parameters
     */
    public static function get_parameters(): external_function_parameters {
        return new external_function_parameters([
            'type' => new external_value(PARAM_INT, 'The type of criteria to manage', VALUE_REQUIRED)
        ]);
    }

    /**
     * Execute and return criteria list
     *
     * @param int $type - The type of criteria to manage
     * @return array
     */
    public static function get($type): array {
        global $DB;
        $params = self::validate_parameters(self::get_parameters(), ['type' => $type]);

        $type = $params['type'];
        $results = [];

        $grids = $DB->get_records('competvet_grid', ['type' => $type], 'sortorder ASC');

        $grids = array_map(function ($grid) {
            global $DB;
            $criteria = $DB->get_records('competvet_criterion', ['evalgridid' => $grid->id], 'sort ASC');
            $gridCriteria = [];
            foreach ($criteria as $criterium) {
                if ($criterium->parentid == 0) {
                    $newCriterium = (object) [
                        'criteriumid' => $criterium->id,
                        'title' => $criterium->label,
                        'idnumber' => $criterium->idnumber,
                        'sortorder' => $criterium->sort,
                        'hasoptions' => $grid->type == COMPETVET_CRITERIA_LIST ? true : false,
                        'options' => []
                    ];
                    foreach ($criteria as $option) {
                        if ($option->parentid === $criterium->id) {
                            $newOption = (object) [
                                'optionid' => $option->id,
                                'idnumber' => $option->idnumber,
                                'title' => $option->label,
                                'sortorder' => $option->sort,
                                'grade' => $option->grade
                            ];
                            $newOption->hasgrade = $grid->type == COMPETVET_CRITERIA_LIST ? true : false;
                            $newCriterium->options[] = $newOption;
                        }
                    }
                    $gridCriteria[] = $newCriterium;
                }
            }
            $newgrid = (object) [
                'gridid' => $grid->id,
                'gridname' => $grid->name,
                'sortorder' => $grid->sortorder,
                'haschanged' => false,
                'criteria' => $gridCriteria
            ];
            return $newgrid;
        }, $grids);
        return [
            'grids' => $grids
        ];
    }

    /**
     * Returns description of method return value
     *
     * @return external_single_structure
     */
    public static function get_returns(): external_single_structure {
        return new external_single_structure([
            'grids' => new external_multiple_structure(
                new external_single_structure([
                    'gridid' => new external_value(PARAM_INT, 'The grid id'),
                    'gridname' => new external_value(PARAM_TEXT, 'The name of the grid'),
                    'sortorder' => new external_value(PARAM_INT, 'The sort order of the grid'),
                    'criteria' => new external_multiple_structure(
                        new external_single_structure([
                            'criteriumid' => new external_value(PARAM_INT, 'The criterium id'),
                            'title' => new external_value(PARAM_TEXT, 'The title of the criterium'),
                            'idnumber' => new external_value(PARAM_TEXT, 'The id number of the criterium'),
                            'sortorder' => new external_value(PARAM_INT, 'The sort order of the criterium'),
                            'hasoptions' => new external_value(PARAM_BOOL, 'Does the criterium have options'),
                            'options' => new external_multiple_structure(
                                new external_single_structure([
                                    'optionid' => new external_value(PARAM_INT, 'The option id'),
                                    'idnumber' => new external_value(PARAM_TEXT, 'The id number of the option'),
                                    'title' => new external_value(PARAM_TEXT, 'The title of the option'),
                                    'sortorder' => new external_value(PARAM_INT, 'The sort order of the option'),
                                    'hasgrade' => new external_value(PARAM_BOOL, 'Does the option have a grade'),
                                    'grade' => new external_value(PARAM_FLOAT, 'The grade of the option'),
                                ])
                            )
                        ])
                    )
                ])
            )
        ]);
    }

}
