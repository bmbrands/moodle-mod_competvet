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

/**
 * TODO describe module globalgrade
 *
 * @module     mod_competvet/local/grading2/components/globalgrade
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import CompetState from '../../competstate';
import Notification from 'core/notification';
import Templates from 'core/templates';
import Repository from '../../new-repository';

const gradingApp = document.querySelector('[data-region="grading-app"]');

const stateTemplate = () => {
    const templateName = 'globalgrade';
    const region = gradingApp.querySelector(`[data-region="${templateName}"]`);
    const template = `mod_competvet/grading2/components/${templateName}`;
    const regionRenderer = (context) => {
        if (context[templateName] === undefined) {
            return;
        }
        Templates.render(template, context).then((html) => {
            region.innerHTML = html;
            return;
        }).catch(Notification.exception);
    };
    CompetState.subscribe(templateName, regionRenderer);
};

const formEvents = () => {
    const form = document.querySelector('[data-region="globalgrade"]');
    form.addEventListener('submit', async(e) => {
        e.preventDefault();
        const state = CompetState.getData();
        const formData = new FormData(form);
        const formObject = {};
        for (const [name, value] of formData.entries()) {
          formObject[name] = value;
        }
        const globalGrade = state.globalgrade;
        globalGrade.userid = state.user.id;
        globalGrade.finalgrade = formObject.finalgrade;
        globalGrade.finalgradeoptions.forEach(element => {
            element.selected = element.value === formObject.finalgrade;
        });
        globalGrade.comment = formObject.comment;
        CompetState.setData({globalgrade: globalGrade});
        Repository.saveGlobalGrade(globalGrade);
    });
};

stateTemplate();
formEvents();