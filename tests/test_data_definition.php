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
use mod_competvet\competvet;
use mod_competvet\local\api\observations;
use mod_competvet\local\persistent\criterion;
use mod_competvet\local\persistent\observation;
use mod_competvet\local\persistent\observation_comment;
use mod_competvet\local\persistent\situation;

/**
 * Competvet Trait for data test definition.
 */
trait test_data_definition {
    /**
     * Prepare scenario
     *
     * @param string $datasetname
     * @return void
     */
    public function prepare_scenario(string $datasetname): void {
        $generator = $this->getDataGenerator();
        $competvetgenerator = $generator->get_plugin_generator('mod_competvet');
        $startdate = new DateTime('last Monday');
        $this->generates_definition(
            $this->{'get_data_definition_' . $datasetname}($startdate->getTimestamp()),
            $generator,
            $competvetgenerator
        );
    }

    /**
     * Generates instances and modules
     *
     * @param array $datadefinition
     * @param object $generator
     * @param object $competvetevalgenerator
     * @return void
     */
    public function generates_definition(array $datadefinition, object $generator, object $competvetevalgenerator): void {
        $users = [];
        foreach ($datadefinition as $coursename => $data) {
            $course = $generator->create_course(['shortname' => $coursename]);
            foreach ($data['users'] as $role => $usernames) {
                foreach ($usernames as $username) {
                    if (empty($users[$username])) {
                        $users[$username] = $generator->create_user(['username' => $username]);
                    }
                    $generator->enrol_user($users[$username]->id, $course->id, $role);
                }
            }
            foreach ($data['groups'] as $groupname => $groupdata) {
                $group = $generator->create_group(['courseid' => $course->id, 'name' => $groupname]);
                foreach ($groupdata['users'] as $username) {
                    $generator->create_group_member(['groupid' => $group->id, 'userid' => $users[$username]->id]);
                }
            }
            foreach ($data['activities'] as $situationname => $situationinfo) {
                $situationmodule = [...$situationinfo];
                $situationmodule['course'] = $course->id;
                $situationmodule['shortname'] = $situationname;
                $situationmodule['name'] = $situationname;
                unset($situationmodule['plannings']);

                $module = $generator->create_module('competvet', $situationmodule);
                $competvet = competvet::get_from_instance_id($module->id);
                $situation = $competvet->get_situation();
                foreach ($situationinfo['plannings'] as $planningdef) {
                    $groupid = groups_get_group_by_name($course->id, $planningdef['groupname']);
                    $planning = $competvetevalgenerator->create_planning([
                        'courseid' => $course->id,
                        'startdate' => $planningdef['startdate'],
                        'enddate' => $planningdef['enddate'],
                        'groupid' => $groupid,
                        'situationid' => $situation->get('id'),
                        'session' => $planningdef['session'],
                    ]);
                    if (!empty($planningdef['observations'])) {
                        foreach ($planningdef['observations'] as $observationdef) {
                            $student = $users[$observationdef['student']];
                            $observer = $users[$observationdef['observer']];
                            $record = [
                                'category' => $observationdef['category'],
                                'status' => $observationdef['status'] ?? observation::STATUS_INPROGRESS,
                                'planningid' => $planning->id,
                                'studentid' => $student->id,
                                'observerid' => $observer->id,
                                'context' => $observationdef['context'] ?? '',
                                'comments' => $observationdef['comments'] ?? [],
                            ];
                            $observation = $competvetevalgenerator->create_observation_with_comment($record);
                            // Now create criteria values.
                            if (!empty($observationdef['criteria'])) {
                                foreach ($observationdef['criteria'] as $criteriondef) {
                                    $criterion = criterion::get_record(['idnumber' => $criteriondef['id']]);
                                    $competvetevalgenerator->create_observation_criterion_value([
                                        'observationid' => $observation->id,
                                        'criterionid' => $criterion->get('id'),
                                        'value' => $criteriondef['value'],
                                    ]);
                                }
                            }
                        }
                    }
                    if (!empty($planningdef['certifications'])) {
                        foreach ($planningdef['certifications'] as $certificationdef) {
                            $student = $users[$certificationdef['student']];
                            $criterion = criterion::get_record(['idnumber' => $certificationdef['criterion']]);
                            $certification = $competvetevalgenerator->create_certification([
                                'studentid' => $student->id,
                                'planningid' => $planning->id,
                                'criterionid' => $criterion->get('id'),
                                'level' => $certificationdef['level'],
                                'comment' => $certificationdef['comment'],
                                'status' => $certificationdef['status'],
                            ]);
                            $competvetevalgenerator->create_certification_validation([
                                'certificationid' => $certification->id,
                                'status' => $certificationdef['validations'][0]['status'],
                                'comment' => $certificationdef['validations'][0]['comment'],
                                'supervisorid' => $users[$certificationdef['validations'][0]['supervisor']]->id,
                            ]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Data definition
     */
    private function get_data_definition_set_1(int $startdate): array {
        $oneweek = 60 * 60 * 24 * 7; // 1 week in seconds.
        $onemonth = $oneweek * 4; // 1 month in seconds.
        return [
            'course 1' => [
                'users' => [
                    'student' => ['student1', 'student2'],
                    'observer' => ['observer1', 'observerandevaluator'],
                    'teacher' => ['teacher1'],
                    'manager' => ['manager'],
                ],
                'groups' => [
                    'group 8.1' => [
                        'users' => ['student1'],
                    ],
                    'group 8.2' => [
                        'users' => ['student2'],
                    ],
                    'group 8.3' => [
                        'users' => [],
                    ],
                    'group 8.4' => [
                        'users' => [],
                    ],
                ],
                'activities' => [
                    'SIT1' => [
                        'category' => 'Y1',
                        'plannings' => [
                            [
                                'startdate' => $startdate,
                                'enddate' => $startdate + $oneweek,
                                'groupname' => 'group 8.1',
                                'session' => '2023',
                            ],
                            [
                                'startdate' => $startdate + $oneweek,
                                'enddate' => $startdate + $oneweek * 2,
                                'groupname' => 'group 8.2',
                                'session' => '2023',
                            ],
                            [
                                'startdate' => $startdate + $onemonth * 12, // Future time.
                                'enddate' => $startdate + $onemonth * 12 + $oneweek,
                                'groupname' => 'group 8.1',
                                'session' => '2030',
                            ],
                        ],
                    ],
                    'SIT2' => [
                        'category' => 'Y2',
                        'plannings' => [
                            [
                                'startdate' => $startdate,
                                'enddate' => $startdate + $oneweek * 2,
                                'groupname' => 'group 8.1',
                                'session' => '2023',
                            ],
                        ],
                    ],
                    'SIT3' => [
                        'category' => 'Y3',
                        'plannings' => [
                            [
                                'startdate' => $startdate,
                                'enddate' => $startdate + $oneweek,
                                'groupname' => 'group 8.1',
                                'session' => '2023',
                            ],
                            [
                                'startdate' => $startdate + $onemonth * 2 + $oneweek,
                                'enddate' => $startdate + $onemonth * 2 + $oneweek * 2,
                                'groupname' => 'group 8.2',
                                'session' => '2023',
                            ],
                        ],
                    ],
                ],
            ],
            'course 2' => [
                'users' => [
                    'student' => ['student1', 'student2', 'student3', 'student4'],
                    'observer' => ['observer2', 'observerandevalandevaluator'],
                    'evaluator' => ['observerandevalandevaluator'],
                    'teacher' => ['teacher2'],
                ],
                'groups' => [
                    'group 8.1' => [
                        'users' => ['student1', 'student2'],
                    ],
                    'group 8.2' => [
                        'users' => ['student3'],
                    ],
                    'group 8.3' => [
                        'users' => ['student4'],
                    ],
                ],
                'activities' => [
                    'SIT4' => [
                        'category' => 'Y1',
                        'plannings' => [
                            [
                                'startdate' => $startdate + $onemonth * 3,
                                'enddate' => $startdate + $onemonth * 3 + $oneweek,
                                'groupname' => 'group 8.1',
                                'session' => '2023',
                            ],
                            [
                                'startdate' => $startdate + $onemonth * 3 + $oneweek,
                                'enddate' => $startdate + $onemonth * 3 + $oneweek * 2,
                                'groupname' => 'group 8.3',
                                'session' => '2023',
                            ],
                        ],
                    ],
                    'SIT5' => [
                        'category' => 'Y2',
                        'plannings' => [
                            [
                                'startdate' => $startdate + $onemonth * 4,
                                'enddate' => $startdate + $onemonth * 4 + $oneweek,
                                'groupname' => 'group 8.2',
                                'session' => '2023',
                            ],
                            [
                                'startdate' => $startdate + $onemonth * 4 + $oneweek,
                                'enddate' => $startdate + $onemonth * 4 + $oneweek * 2,
                                'groupname' => 'group 8.3',
                                'session' => '2023',
                            ],
                        ],
                    ],
                    'SIT6' => [
                        'category' => 'Y3',
                        'plannings' => [
                            [
                                'startdate' => $startdate + $onemonth * 5,
                                'enddate' => $startdate + $onemonth * 5 + $oneweek,
                                'groupname' => 'group 8.3',
                                'session' => '2023',
                            ],
                        ],
                    ],
                ],
            ],
            'course 3' => [
                'users' => [
                    'student' => ['student1', 'student2', 'student3', 'student4', 'studentandobserver'],
                    'observer' => ['observer2', 'studentandobserver'],
                    'evaluator' => ['observerandevaluator'],
                ],
                'groups' => [
                    'group 8.1' => [
                        'users' => ['student1', 'student2', 'studentandobserver'],
                    ],
                    'group 8.3' => [
                        'users' => ['student3'],
                    ],
                    'group 8.4' => [
                        'users' => ['student4'],
                    ],
                ],
                'activities' => [
                    'SIT7' => [
                        'category' => 'Y1',
                        'plannings' => [
                            [
                                'startdate' => $startdate + $onemonth * 6,
                                'enddate' => $startdate + $onemonth * 6 + $oneweek,
                                'groupname' => 'group 8.1',
                                'session' => '2023',
                            ],
                        ],
                    ],
                    'SIT8' => [
                        'category' => 'Y2',
                        'plannings' => [
                            [
                                'startdate' => $startdate + $onemonth * 7,
                                'enddate' => $startdate + $onemonth * 7 + $oneweek,
                                'groupname' => 'group 8.3',
                                'session' => '2023',
                            ],
                            [
                                'startdate' => $startdate + $onemonth * 12, // Future time.
                                'enddate' => $startdate + $onemonth * 12 + $oneweek,
                                'session' => '2030',
                                'groupname' => 'group 8.3',
                            ],

                        ],
                    ],
                    'SIT9' => [
                        'category' => 'Y3',
                        'plannings' => [
                            [
                                'startdate' => $startdate + $onemonth * 8,
                                'enddate' => $startdate + $onemonth * 8 + $oneweek,
                                'groupname' => 'group 8.4',
                                'session' => '2023',
                            ],
                            [
                                'startdate' => $startdate + $onemonth * 12, // Future time.
                                'enddate' => $startdate + $onemonth * 12 + $oneweek,
                                'groupname' => 'group 8.4',
                                'session' => '2030',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Data definition
     */
    private function get_data_definition_set_2(int $startdate): array {
        $oneweek = 60 * 60 * 24 * 7; // 1 week in seconds.
        $onemonth = $oneweek * 4; // 1 month in seconds.
        return [
            'course 1' => [
                'users' => [
                    'student' => ['student1', 'student2'],
                    'observer' => ['observer1', 'observer2'],
                    'teacher' => ['teacher1'],
                    'manager' => ['manager'],
                ],
                'groups' => [
                    'group 8.1' => [
                        'users' => ['student1'],
                    ],
                    'group 8.2' => [
                        'users' => ['student2'],
                    ],
                    'group 8.3' => [
                        'users' => [],
                    ],
                    'group 8.4' => [
                        'users' => [],
                    ],
                ],
                'activities' => [
                    'SIT1' => [
                        'category' => 'Y1',
                        'plannings' => [
                            [
                                'startdate' => $startdate,
                                'enddate' => $startdate + $oneweek,
                                'groupname' => 'group 8.1',
                                'session' => '2023',
                            ],
                            [
                                'startdate' => $startdate + $oneweek,
                                'enddate' => $startdate + $oneweek * 2,
                                'groupname' => 'group 8.2',
                                'session' => '2023',
                            ],
                            [
                                'startdate' => $startdate + $onemonth * 12, // Future time.
                                'enddate' => $startdate + $onemonth * 12 + $oneweek,
                                'groupname' => 'group 8.1',
                                'session' => '2030',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Data definition
     */
    private function get_data_definition_set_3(int $startdate): array {
        $oneweek = 60 * 60 * 24 * 7; // 1 week in seconds.
        $onemonth = $oneweek * 4; // 1 month in seconds.
        return [
            'course 1' => [
                'users' => [
                    'student' => ['student1', 'student2'],
                    'observer' => ['observer1', 'observer2'],
                    'teacher' => ['teacher1'],
                    'manager' => ['manager'],
                ],
                'groups' => [
                    'group 8.1' => [
                        'users' => ['student1'],
                    ],
                    'group 8.2' => [
                        'users' => ['student2'],
                    ],
                    'group 8.3' => [
                        'users' => [],
                    ],
                    'group 8.4' => [
                        'users' => [],
                    ],
                ],
                'activities' => [
                    'SIT1' => [
                        'category' => 'Y1',
                        'plannings' => [
                            [
                                'startdate' => $startdate,
                                'enddate' => $startdate + $oneweek,
                                'groupname' => 'group 8.1',
                                'session' => '2023',
                                'observations' => [
                                    [
                                        'category' => observation::CATEGORY_EVAL_AUTOEVAL,
                                        'student' => 'student1',
                                        'observer' => 'student1',
                                        'context' => 'A context for autoeval',
                                        'comments' => [
                                            ['type' => observation_comment::OBSERVATION_COMMENT, 'comment' => 'A comment'],
                                            ['type' => observation_comment::AUTOEVAL_OBSERVER_COMMENT,
                                                'comment' => 'Another comment', ],
                                        ],
                                        'criteria' => [
                                            ['id' => 'Q001', 'value' => 1],
                                            ['id' => 'Q002', 'value' => 'Comment autoeval 1'],
                                            ['id' => 'Q003', 'value' => 'Comment autoeval 2'],
                                        ],
                                    ],
                                    [
                                        'category' => observation::CATEGORY_EVAL_OBSERVATION,
                                        'student' => 'student1',
                                        'observer' => 'observer1',
                                        'context' => 'A context for observation',
                                        'comments' => [
                                            ['type' => observation_comment::OBSERVATION_COMMENT, 'comment' => 'A comment'],
                                            ['type' => observation_comment::OBSERVATION_PRIVATE_COMMENT,
                                                'comment' => 'Another comment', ],
                                        ],
                                        'criteria' => [
                                            ['id' => 'Q001', 'value' => 5],
                                            ['id' => 'Q002', 'value' => 'Comment eval 1'],
                                            ['id' => 'Q003', 'value' => 'Comment eval 2'],
                                        ],
                                    ],
                                ],
                                'certifications' => [
                                    [
                                        'student' => 'student1',
                                        'planning' => 'planning1',
                                        'criterion' => 'CERT1',
                                        'level' => 50,
                                        'comment' => 'A comment',
                                        'status' => 'cert:seendone',
                                        'validations' => [
                                            ['status' => 'certvalid:confirmed', 'comment' => 'A comment', 'supervisor' => 'observer1'],
                                            ['status' => 'certvalid:notseen', 'comment' => 'A comment', 'supervisor' => 'observer2'],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'startdate' => $startdate + $oneweek,
                                'enddate' => $startdate + $oneweek * 2,
                                'groupname' => 'group 8.2',
                                'session' => '2023',
                            ],
                            [
                                'startdate' => $startdate + $onemonth * 12, // Future time.
                                'enddate' => $startdate + $onemonth * 12 + $oneweek,
                                'groupname' => 'group 8.1',
                                'session' => '2030',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
